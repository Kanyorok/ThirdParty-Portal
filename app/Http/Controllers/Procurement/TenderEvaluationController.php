<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\TenderSection;
use App\Models\Procurement\TenderCommitteeMember;
use App\Models\Procurement\TenderCommitteeEvaluation;
use App\Enums\Core\PermissionEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TenderEvaluationController extends Controller
{
    /**
     * Show the section-based evaluation form for a specific bid
     */
    public function showEvaluationForm(Request $request, $bidId)
    {
        $this->authorize(PermissionEnum::BidSubmissionRead);

        $bid = BidSubmission::with(['supplier.thirdParty'])
            ->findOrFail($bidId);

        // Find tender by bid reference
        $tender = Tender::with(['tenderSections.sections.criteria'])
            ->where('TenderNo', $bid->TenderRef)
            ->firstOrFail();

        // Enforce membership: accepted & active for this tender
        $currentUserId = Auth::id();
        $committeeMember = $this->findAcceptedCommitteeMember($tender->Id, $currentUserId);

        if (!$committeeMember) {
            return redirect()->route('evaluationdashboard.index')
                ->with('error', 'You are not authorized to evaluate this tender.');
        }

        // Filter out inactive or orphaned tender sections before readiness check
        $tender->setRelation('tenderSections', $tender->tenderSections->filter(function($ts){
            return ($ts->IsActive ?? true) && $ts->sections; // ensure active and has linked Section
        })->values());

        // Check if tender has valid section configuration
        $readiness = $tender->getEvaluationReadiness();
        if (!$readiness['ready']) {
            return redirect()->route('evaluationdashboard.index')
                ->with('error', $readiness['message']);
        }

        // Enforce: if member already evaluated, block re-evaluation
        $existingMemberId = $committeeMember->Id;
        if ($existingMemberId) {
            $alreadyEvaluated = TenderCommitteeEvaluation::where('TenderID', $tender->Id)
                ->where('MemberID', $existingMemberId)
                ->exists();
            if ($alreadyEvaluated) {
                return redirect()->route('evaluationdashboard.index')
                    ->with('error', 'You have already submitted an evaluation for this tender.');
            }
        }

        // Get existing evaluation scores for this committee member
        $existingScores = TenderCommitteeEvaluation::where('TenderID', $tender->Id)
            ->where('MemberID', $committeeMember->Id)
            ->get()
            ->keyBy(function ($evaluation) {
                return $evaluation->SectionID . '_' . $evaluation->CriteriaID;
            });

        // Only allow criteria that were explicitly selected for this tender
        $selectedBySection = \App\Models\Procurement\TenderCriteria::where('TenderID', $tender->Id)
            ->where('IsActive', true)
            ->get(['SectionID','CriteriaID'])
            ->groupBy('SectionID')
            ->map(fn($rows) => $rows->pluck('CriteriaID')->values());

        // Filter each section's criteria collection in-place to only selected criteria
        $tender->setRelation('tenderSections', $tender->tenderSections->map(function ($ts) use ($selectedBySection) {
            $section = $ts->sections;
            $allowed = collect($selectedBySection->get($section->Id, collect()))->map(fn($v) => (int)$v)->all();
            if ($section && $section->relationLoaded('criteria')) {
                $filtered = $section->criteria->whereIn('Id', $allowed)->values();
                $section->setRelation('criteria', $filtered);
            }
            return $ts;
        }));

        // Get all committee members for this tender (for progress tracking)
        $allMembers = TenderCommitteeMember::where('TenderID', $tender->Id)
            ->where('Response', 1)
            ->with(['user.employee'])
            ->get();

        return view('procurement.tendering.bidopeningandevaluation.evaluation.section-based-form', [
            'tender' => $tender,
            'bid' => $bid,
            'tenderSections' => $tender->tenderSections, // Pivot records with weights
            'committeeMember' => $committeeMember,
            'existingScores' => $existingScores,
            'allMembers' => $allMembers,
            'evaluationProgress' => $this->getEvaluationProgress($tender->Id, $committeeMember->Id)
        ]);
    }

    /**
     * Submit section-based evaluation scores
     */
    public function submitEvaluation(Request $request, $bidId)
    {
        $this->authorize(PermissionEnum::BidSubmissionWrite);

        $bid = BidSubmission::findOrFail($bidId);

        // Find tender
        $tender = Tender::where('TenderNo', $bid->TenderRef)->firstOrFail();

        // Verify committee membership (robust check)
        $currentUserId = Auth::id();
        $committeeMember = $this->findAcceptedCommitteeMember($tender->Id, $currentUserId);

        if (!$committeeMember) {
            return response()->json(['error' => 'Unauthorized: Not an accepted committee member'], 403);
        }

        // Debug: Log what we found
        Log::info('Committee Member Found:', [
            'id' => $committeeMember->id,
            'Id' => $committeeMember->Id ?? 'NULL',
            'primary_key' => $committeeMember->getKey(),
            'all_attributes' => $committeeMember->getAttributes(),
            'exists' => $committeeMember->exists
        ]);

        // Validate request data
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.section_id' => 'required|exists:t_Sections,Id',
            'scores.*.criteria_id' => 'required|exists:t_Criterias,Id',
            'scores.*.score' => 'required|numeric|min:0|max:10',
            'evaluation_notes' => 'nullable|string|max:2000',
            'evaluation_type' => 'required|in:draft,final'
        ]);

        DB::beginTransaction();

        try {
            // Validate that all required sections/criteria are scored
            $tenderSections = TenderSection::where('TenderID', $tender->Id)->get();

            // Only require the criteria selected for this tender (t_TenderCriteria)
            $selectedCriteriaRows = \App\Models\Procurement\TenderCriteria::where('TenderID', $tender->Id)
                ->where('IsActive', true)
                ->get(['SectionID','CriteriaID']);
            $requiredScores = $selectedCriteriaRows->map(function ($row) {
                return $row->SectionID . '_' . $row->CriteriaID;
            })->all();

            $submittedScores = collect($validated['scores'])
                ->mapWithKeys(function ($score) {
                    return [$score['section_id'] . '_' . $score['criteria_id'] => $score['score']];
                });

            // For final submission, ensure all criteria are scored
            if ($validated['evaluation_type'] === 'final') {
                $missingScores = array_diff($requiredScores, $submittedScores->keys()->toArray());
                if (!empty($missingScores)) {
                    return response()->json([
                        'error' => 'Please score all criteria before final submission.',
                        'missing_criteria' => $missingScores
                    ], 422);
                }
            }

            // Delete existing scores for this member
            TenderCommitteeEvaluation::where('TenderID', $tender->Id)
                ->where('MemberID', $committeeMember->Id)
                ->delete();

            // Create new evaluation records
            $totalWeightedScore = 0;
            $sectionScores = [];

            foreach ($validated['scores'] as $scoreData) {
                $sectionId = $scoreData['section_id'];
                $criteriaId = $scoreData['criteria_id'];
                $score = $scoreData['score'];

                // Debug: Log the exact values before insert
                $memberId = $committeeMember->getKey() ?? $committeeMember->id ?? $committeeMember->Id;
                $insertData = [
                    'CommitteeID' => $committeeMember->CommitteeID,
                    'TenderID' => $tender->Id,
                    'MemberID' => $memberId, // Use multiple fallbacks to ensure we get the ID
                    'SectionID' => $sectionId,
                    'CriteriaID' => $criteriaId,
                    'Score' => $score,
                    'MaxScore' => 10,
                    'IsActive' => true,
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ];

                // Include SupplierId only if the column exists in the table
                if (Schema::hasColumn('t_TenderCommitteeEvaluations', 'SupplierId')) {
                    $insertData['SupplierId'] = $bid->SupplierId;
                }

                Log::info('About to insert TenderCommitteeEvaluation with data:', $insertData);

                // Create committee evaluation record
                TenderCommitteeEvaluation::create($insertData);

                // Track section scores for weighted calculation
                if (!isset($sectionScores[$sectionId])) {
                    $sectionScores[$sectionId] = [
                        'total_score' => 0,
                        'criteria_count' => 0,
                        'weight' => $tenderSections->firstWhere('SectionID', $sectionId)->Weight ?? 0
                    ];
                }

                $sectionScores[$sectionId]['total_score'] += $score;
                $sectionScores[$sectionId]['criteria_count']++;
            }

            // Calculate weighted total score using correct formula:
            // Section Score = (Sum of Criteria Scores / Number of Criteria) × 10 × Section Weight
            foreach ($sectionScores as $sectionData) {
                $sectionAverage = $sectionData['total_score'] / $sectionData['criteria_count'];
                $weightedScore = ($sectionAverage * 10 * $sectionData['weight']) / 100; // Divide by 100 since weight is percentage
                $totalWeightedScore += $weightedScore;
            }

            // Update bid with evaluation data if final submission
            if ($validated['evaluation_type'] === 'final') {
                $bid->update([
                    'TechnicalScore' => $totalWeightedScore, // Using as overall score
                    'FinancialScore' => null, // Will be calculated separately if needed
                    'TotalScore' => $totalWeightedScore,
                    'BidStatus' => 'evaluated',
                    'EvaluationNotes' => $validated['evaluation_notes'],
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Mark committee member as having evaluated
                $committeeMember->update([
                    'HasEvaluated' => true,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);
            }

            // Log evaluation activity
            activity()
                ->performedOn($bid)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'section_based_evaluation',
                    'evaluation_type' => $validated['evaluation_type'],
                    'total_score' => $totalWeightedScore,
                    'sections_evaluated' => count($sectionScores),
                    'criteria_scored' => count($validated['scores'])
                ])
                ->log("Section-based evaluation " . ($validated['evaluation_type'] === 'final' ? 'completed' : 'saved as draft') .
                    " for {$bid->SupplierName} - Score: " . round($totalWeightedScore, 2));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $validated['evaluation_type'] === 'final'
                    ? 'Evaluation completed successfully!'
                    : 'Evaluation saved as draft.',
                'total_score' => round($totalWeightedScore, 2),
                'evaluation_type' => $validated['evaluation_type'],
                'redirect' => $validated['evaluation_type'] === 'final'
                    ? route('evaluationdashboard.index')
                    : null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'section_based_evaluation_failed',
                    'bid_id' => $bidId,
                    'error' => $e->getMessage()
                ])
                ->log('Failed to submit section-based evaluation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to submit evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    private function findAcceptedCommitteeMember(int $tenderId, int $userId): ?\App\Models\Procurement\TenderCommitteeMember
    {
        $row = DB::table('t_TenderCommitteeMembers as m')
            ->leftJoin('t_TenderCommittee as c', 'c.Id', '=', 'm.CommitteeID')
            ->join('t_Users as u', function ($join) {
                $join->on('u.Id', '=', 'm.UserID')
                    ->orOn('u.EmployeeId', '=', 'm.UserID');
            })
            ->where('u.Id', $userId)
            ->where(function ($q) use ($tenderId) {
                $q->where('m.TenderID', $tenderId)
                    ->orWhere('c.ReferenceId', $tenderId);
            })
            ->where('m.IsActive', 1)
            ->where(function ($q) {
                $q->whereNull('m.Response')->orWhere('m.Response', 1);
            })
            ->select('m.Id')
            ->orderByDesc('m.Id')
            ->first();

        return $row ? TenderCommitteeMember::find($row->Id) : null;
    }

    /**
     * Get evaluation progress for a committee member
     */
    private function getEvaluationProgress($tenderId, $memberId)
    {
        // Count only criteria explicitly selected for this tender
        $totalCriteria = DB::table('t_TenderCriteria as tc')
            ->where('tc.TenderID', $tenderId)
            ->where('tc.IsActive', true)
            ->count();

        $completedCriteria = TenderCommitteeEvaluation::where('TenderID', $tenderId)
            ->where('MemberID', $memberId)
            ->count();

        return [
            'total' => $totalCriteria,
            'completed' => $completedCriteria,
            'percentage' => $totalCriteria > 0 ? round(($completedCriteria / $totalCriteria) * 100, 1) : 0
        ];
    }

    /**
     * Get evaluation summary for a tender (for consolidation)
     */
    public function getEvaluationSummary($tenderId)
    {
        $tender = Tender::with(['submissions', 'tenderSections.sections'])
            ->findOrFail($tenderId);

        $evaluations = TenderCommitteeEvaluation::with(['tenderCommitteeMember', 'section', 'criteria'])
            ->where('TenderID', $tenderId)
            ->get()
            ->groupBy('MemberID');

        $summaryData = [];

        foreach ($tender->submissions()->where('BidStatus', 'evaluated')->get() as $bid) {
            $bidEvaluations = [];

            foreach ($evaluations as $memberId => $memberEvaluations) {
                // Calculate member's score for this bid
                // This is a simplified version - in practice, you'd need to link evaluations to specific bids
                $memberScore = $memberEvaluations->sum(function ($evaluation) use ($tender) {
                    // Apply section weight to criteria score
                    $section = $evaluation->section;
                    $tenderSection = $tender->tenderSections->firstWhere('SectionID', $section->Id);
                    $weight = $tenderSection ? $tenderSection->Weight : 0;

                    return ($evaluation->Score / 10) * $weight;
                });

                $bidEvaluations[] = [
                    'member_id' => $memberId,
                    'member_name' => $memberEvaluations->first()->tenderCommitteeMember->employee->full_name ?? 'Unknown',
                    'score' => $memberScore
                ];
            }

            $summaryData[] = [
                'bid' => $bid,
                'evaluations' => $bidEvaluations,
                'average_score' => collect($bidEvaluations)->avg('score'),
                'score_variance' => $this->calculateScoreVariance($bidEvaluations)
            ];
        }

        return $summaryData;
    }

    /**
     * Calculate score variance among committee members
     */
    private function calculateScoreVariance($evaluations)
    {
        if (count($evaluations) < 2) return 0;

        $scores = collect($evaluations)->pluck('score');
        $mean = $scores->avg();
        $variance = $scores->map(function ($score) use ($mean) {
                return pow($score - $mean, 2);
            })->sum() / (count($evaluations) - 1);

        return sqrt($variance); // Standard deviation
    }
}
