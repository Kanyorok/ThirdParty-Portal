<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Criteria;
use App\Models\Procurement\Section;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderCommitteeEvaluation;
use App\Models\Procurement\TenderSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BidScoreConsolidationController extends Controller
{
    /**
     * Display consolidated scores for a specific tender
     */
    public function index(Request $request)
    {
        $tenderId = $request->get('tender_id');

        if (! $tenderId) {
            // If no tender specified, show only tenders that:
            // 1. Have completed evaluation (submissions with BidStatus = 'evaluated')
            // 2. Have NOT been awarded yet (no approved TenderAward)
            $tenders = Tender::whereHas('submissions', function ($query) {
                $query->where('IsResponsive', true)
                      ->where('BidStatus', 'evaluated'); // Only show tenders with completed evaluations
            })
                // Exclude tenders that have approved awards
                ->whereDoesntHave('awards', function ($query) {
                    $query->where('AwardStatus', \App\Models\Procurement\TenderAward::STATUS_APPROVED);
                })
                ->select('Id', 'Title', 'TenderNo')
                ->orderByDesc('OpeningDate')
                ->get();

            return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.select', compact('tenders'));
        }

        $consolidatedData = $this->getConsolidatedScores($tenderId);

        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.index', $consolidatedData);
    }

    /**
     * Calculate consolidated scores for all bidders in a tender
     */
    protected function getConsolidatedScores($tenderId)
    {
        $tender = Tender::findOrFail($tenderId);

        // Get responsive bids from BidSubmissions table (including evaluated ones)
        $bidders = \App\Models\Procurement\BidSubmission::where('TenderRef', $tender->TenderNo)
            ->where('IsResponsive', true)
            ->whereIn('BidStatus', ['responsive', 'evaluated']) // Include both responsive and evaluated bids
            ->select('Id', 'SupplierId', 'SupplierName', 'BidAmount', 'Currency')
            ->get()
            ->map(function ($bid) {
                return [
                    'id' => $bid->SupplierId,
                    'bid_id' => $bid->Id,
                    'name' => $bid->SupplierName ?? 'Unknown Supplier',
                    'bid_amount' => $bid->BidAmount,
                    'currency' => $bid->Currency,
                ];
            });

        // Get tender sections with their weights and tender-specific criteria (with MaxScore)
        $sections = $this->getTenderSections($tenderId);

        // Get all evaluations for this tender including SupplierId
        $rawEvaluations = TenderCommitteeEvaluation::where('TenderID', $tenderId)
            ->select('MemberID', 'SupplierId', 'SectionID', 'CriteriaID', 'Score')
            ->get();

        // Existing award (to disable/hide award actions in the UI)
        // Active award (Pending or Approved) disables Award buttons
        $activeAward = \App\Models\Procurement\TenderAward::with(['winningSupplier.thirdParty'])
            ->where('TenderID', $tenderId)
            ->whereIn('AwardStatus', [\App\Models\Procurement\TenderAward::STATUS_PENDING, \App\Models\Procurement\TenderAward::STATUS_APPROVED])
            ->orderByDesc('Id')
            ->first();
        $awardBlocks = (bool)$activeAward;

        // Build section weights map
        $sectionWeights = collect($sections)->mapWithKeys(function ($sec) {
            return [$sec['id'] => $sec['weight']];
        });

        // Build accepted evaluators list with names
        $evaluators = DB::table('t_TenderCommitteeMembers as m')
            ->join('t_Users as u', function ($join) {
                $join->on('u.Id', '=', 'm.UserID')
                     ->orOn('u.EmployeeId', '=', 'm.UserID');
            })
            ->leftJoin('t_Employees as e', 'e.Id', '=', 'u.EmployeeId')
            ->where('m.TenderID', $tenderId)
            ->where('m.IsActive', 1)
            ->where('m.Response', 1)
            ->select('m.Id as MemberID', 'u.Name as UserName', 'e.FirstName', 'e.LastName')
            ->orderBy('m.Id')
            ->get()
            ->map(function ($row) {
                $name = trim(($row->FirstName ? $row->FirstName . ' ' : '') . ($row->LastName ?? ''));
                if ($name === '') {
                    $name = $row->UserName ?? ('Member #' . $row->MemberID);
                }

                return [
                    'id' => (int)$row->MemberID,
                    'name' => $name,
                ];
            })->values();

        // Group evaluations by Supplier -> Member -> Section
        $grouped = $rawEvaluations->groupBy(['SupplierId', 'MemberID', 'SectionID']);

        // Pre-build per-section criteria MaxScore maps for quick lookup
        $sectionCriteriaMax = collect($sections)->mapWithKeys(function ($sec) {
            $map = collect($sec['criteria'] ?? [])->mapWithKeys(function ($c) {
                return [ (int)$c['id'] => (float)($c['max_score'] ?? 10) ];
            });

            return [ (int)$sec['id'] => $map ];
        });

        // Compute per-evaluator total (0-100) per supplier, then consolidated average
        $supplierSummaries = [];
        foreach ($bidders as $bidder) {
            $supplierId = $bidder['id'];
            $memberScores = [];

            if ($grouped->has($supplierId)) {
                foreach ($grouped[$supplierId] as $memberId => $sectionsGrouped) {
                    $memberTotal = 0;
                    foreach ($sections as $sec) {
                        $sectionId = $sec['id'];
                        $weight = (float)($sectionWeights[$sectionId] ?? 0);
                        if ($weight <= 0) {
                            continue;
                        }
                        $criteriaRows = collect($sectionsGrouped[$sectionId] ?? []);
                        if ($criteriaRows->isEmpty()) {
                            continue;
                        }
                        // Group evaluator's scores by CriteriaID and average if multiple rows exist
                        $byCriteria = $criteriaRows->groupBy('CriteriaID');
                        $sumScore = 0.0;
                        $sumMax = 0.0;
                        $critMax = $sectionCriteriaMax->get((int)$sectionId) ?? collect();
                        foreach ($byCriteria as $critId => $rows) {
                            $avg = (float)collect($rows)->avg('Score');
                            $max = (float)($critMax->get((int)$critId) ?? 10.0);
                            $sumScore += $avg;
                            $sumMax += $max;
                        }
                        $sectionPercent = $sumMax > 0 ? ($sumScore / $sumMax) * 100.0 : 0.0;
                        $memberTotal += ($sectionPercent * $weight) / 100.0;
                    }
                    $memberScores[(int)$memberId] = round($memberTotal, 2);
                }
            }

            $scoresOnly = array_values($memberScores);
            $consolidatedAverage = count($scoresOnly) > 0 ? round(array_sum($scoresOnly) / count($scoresOnly), 2) : 0.0;

            $supplierSummaries[] = [
                'supplier_id' => $supplierId,
                'supplier_name' => $bidder['name'],
                'evaluator_scores' => $memberScores,
                'average' => $consolidatedAverage,
                'has_award' => $awardBlocks,
                'is_awarded' => $activeAward ? ((int)$activeAward->WinningSupplierID === (int)$supplierId) : false,
            ];
        }

        // Sort by consolidated average desc and assign ranks
        usort($supplierSummaries, function ($a, $b) {
            return $b['average'] <=> $a['average'];
        });
        foreach ($supplierSummaries as $idx => &$row) {
            $row['rank'] = $idx + 1;
        }

        return [
            'tender' => $tender,
            'bidders' => $bidders,
            'evaluators' => $evaluators,
            'supplierSummaries' => $supplierSummaries,
            'evaluatorCount' => $evaluators->count(),
            // For header display (award info), use the active award if present
            'existingAward' => $activeAward,
            'awardBlocks' => $awardBlocks,
        ];
    }

    /**
     * Get tender sections with their criteria and weights
     */
    protected function getTenderSections($tenderId)
    {
        // Align with award page: use TenderSection + TenderCriteria (MaxScore), ignore soft-deleted/disabled
        $rows = TenderSection::where('TenderID', $tenderId)
            ->where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->with(['sections'])
            ->orderBy('Id', 'desc')
            ->get()
            ->unique('SectionID')
            ->sortByDesc('Weight')
            ->values();

        return $rows->map(function ($ts) use ($tenderId) {
            $section = $ts->sections;
            $tc = \App\Models\Procurement\TenderCriteria::where('TenderID', $tenderId)
                ->where('SectionID', $section->Id)
                ->where('IsActive', 1)
                ->whereNull('DeletedOn')
                ->with('criteria')
                ->get();

            return [
                'id' => $section->Id,
                'name' => $section->SectionName,
                'weight' => (float)($ts->Weight ?? 100),
                'criteria' => $tc->map(function ($row) {
                    return [
                        'id' => (int)$row->CriteriaID,
                        'name' => $row->criteria?->CriteriaName ?? 'Criteria',
                        'max_score' => (float)($row->MaxScore ?? 10),
                    ];
                })->values(),
            ];
        })->values();
    }

    /**
     * Get criteria for a section
     */
    protected function getSectionCriteria($sectionId)
    {
        return Criteria::where('SectionID', $sectionId)
            ->select('id', 'Name', 'Weight', 'MaxScore')
            ->get()
            ->map(function ($criteria) {
                return [
                    'id' => $criteria->id,
                    'name' => $criteria->Name,
                    'weight' => $criteria->Weight ?? 10,
                    'max_score' => $criteria->MaxScore ?? 10,
                ];
            });
    }

    /**
     * Calculate consolidated score for a specific bidder
     */
    protected function calculateBidderScore($bidder, $sections, $evaluations, $tenderId)
    {
        $sectionScores = [];
        $totalWeightedScore = 0;
        $totalSectionWeight = 0;

        foreach ($sections as $section) {
            $sectionScore = $this->calculateSectionScore($section, $evaluations, $tenderId);
            $sectionWeightedScore = ($sectionScore / 100) * $section['weight'];

            $sectionScores[] = [
                'section_id' => $section['id'],
                'section_name' => $section['name'],
                'score' => $sectionScore,
                'weight' => $section['weight'],
                'weighted_score' => $sectionWeightedScore,
            ];

            $totalWeightedScore += $sectionWeightedScore;
            $totalSectionWeight += $section['weight'];
        }

        // Normalize to percentage if total weights don't equal 100
        if ($totalSectionWeight != 100 && $totalSectionWeight > 0) {
            $totalWeightedScore = ($totalWeightedScore / $totalSectionWeight) * 100;
        }

        return [
            'bidder_id' => $bidder['id'],
            'bidder_name' => $bidder['name'],
            'section_scores' => $sectionScores,
            'total_weighted_score' => round($totalWeightedScore, 2),
        ];
    }

    /**
     * Calculate average score for a section across all evaluators
     */
    protected function calculateSectionScore($section, $evaluations, $tenderId)
    {
        $totalCriteriaScore = 0;
        $criteriaCount = 0;

        foreach ($section['criteria'] as $criteria) {
            $criteriaEvaluations = collect($evaluations[$section['id']][$criteria['id']] ?? []);

            if ($criteriaEvaluations->isNotEmpty()) {
                // Average evaluator score for this criterion
                $averageScore = $criteriaEvaluations->avg('Score');
                $totalCriteriaScore += $averageScore;
                $criteriaCount++;
            }
        }

        if ($criteriaCount > 0) {
            $sectionAverage = $totalCriteriaScore / $criteriaCount; // out of 10

            return ($sectionAverage / 10) * 100; // percentage
        }

        return 0;
    }

    /**
     * Get number of evaluators for this tender
     */
    protected function getEvaluatorCount($tenderId)
    {
        // Prefer accepted committee members (Response = 1) assigned to this tender
        $accepted = DB::table('t_TenderCommitteeMembers')
            ->where('TenderID', $tenderId)
            ->where('IsActive', 1)
            ->where('Response', 1)
            ->count();

        if ($accepted > 0) {
            return $accepted;
        }

        // Fallback for committees created via TenderCommittee (CommitteeType/ReferenceId linkage)
        return DB::table('t_TenderCommitteeMembers as m')
            ->join('t_TenderCommittee as c', 'c.Id', '=', 'm.CommitteeID')
            ->where('c.CommitteeType', 'tender')
            ->where('c.ReferenceId', $tenderId)
            ->where('m.IsActive', 1)
            ->where('m.Response', 1)
            ->count();
    }

    /**
     * Get recommendation based on rank and score
     */
    protected function getRecommendation($rank, $score)
    {
        if ($rank === 1 && $score >= 70) {
            return ['status' => 'Recommended', 'class' => 'bg-success'];
        } elseif ($rank === 2 && $score >= 60) {
            return ['status' => 'Backup', 'class' => 'bg-secondary'];
        } else {
            return ['status' => 'Not Recommended', 'class' => 'bg-danger'];
        }
    }

    /**
     * Show section-wise drill-down for a specific tender
     */
    public function sectionDrilldown($tenderId)
    {
        $tender = Tender::findOrFail($tenderId);
        $sections = $this->getTenderSections($tenderId);

        // Optional supplier filter
        $supplierId = request()->query('supplier_id');

        // Get all evaluations grouped by section and evaluator (optionally filtered by supplier)
        $evaluations = TenderCommitteeEvaluation::where('TenderID', $tenderId)
            ->when($supplierId, fn ($q) => $q->where('SupplierId', $supplierId))
            ->with(['tenderCommitteeMember'])
            ->get()
            ->groupBy(['SectionID', 'MemberID']);

        $sectionDetails = [];
        foreach ($sections as $section) {
            $sectionEvaluations = $evaluations[$section['id']] ?? collect();

            $evaluatorScores = [];
            foreach ($sectionEvaluations as $memberId => $memberEvaluations) {
                $member = $memberEvaluations->first()->tenderCommitteeMember ?? null;
                $criteriaScores = [];
                $totalScore = 0;
                $criteriaCount = 0;

                foreach ($memberEvaluations as $evaluation) {
                    $criteriaScores[] = [
                        'criteria_id' => $evaluation->CriteriaID,
                        'score' => $evaluation->Score,
                    ];
                    $totalScore += $evaluation->Score;
                    $criteriaCount++;
                }

                $averageScore = $criteriaCount > 0 ? $totalScore / $criteriaCount : 0;

                $evaluatorScores[] = [
                    'member_id' => $memberId,
                    'evaluator_name' => $member ? "User #{$member->UserID}" : 'Unknown',
                    'role' => $member ? $member->Role : 'Unknown',
                    'criteria_scores' => $criteriaScores,
                    'average_score' => round($averageScore, 2),
                ];
            }

            $sectionDetails[] = [
                'section' => $section,
                'evaluator_scores' => $evaluatorScores,
            ];
        }

        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.section-drilldown', [
            'tender' => $tender,
            'sectionDetails' => $sectionDetails,
        ]);
    }

    /**
     * Show evaluator-wise drill-down for a specific tender
     */
    public function evaluatorDrilldown($tenderId)
    {
        $tender = Tender::findOrFail($tenderId);
        $sections = $this->getTenderSections($tenderId);

        // Optional supplier filter
        $supplierId = request()->query('supplier_id');

        // Get all evaluations grouped by evaluator and section (optionally filtered by supplier)
        $evaluations = TenderCommitteeEvaluation::where('TenderID', $tenderId)
            ->when($supplierId, fn ($q) => $q->where('SupplierId', $supplierId))
            ->with(['tenderCommitteeMember'])
            ->get()
            ->groupBy(['MemberID', 'SectionID']);

        $evaluatorDetails = [];
        foreach ($evaluations as $memberId => $memberEvaluations) {
            $member = $memberEvaluations->flatten()->first()->tenderCommitteeMember ?? null;

            $sectionScores = [];
            $totalWeightedScore = 0;
            $totalWeight = 0;

            foreach ($sections as $section) {
                $sectionEvaluations = $memberEvaluations[$section['id']] ?? collect();

                if ($sectionEvaluations->isNotEmpty()) {
                    $sectionScore = $sectionEvaluations->avg('Score');
                    $sectionPercentage = ($sectionScore / 10) * 100;
                    $weightedScore = ($sectionPercentage * $section['weight']) / 100;

                    $sectionScores[] = [
                        'section_id' => $section['id'],
                        'section_name' => $section['name'],
                        'section_weight' => $section['weight'],
                        'raw_score' => round($sectionScore, 2),
                        'percentage_score' => round($sectionPercentage, 2),
                        'weighted_score' => round($weightedScore, 2),
                        'criteria_count' => $sectionEvaluations->count(),
                    ];

                    $totalWeightedScore += $weightedScore;
                    $totalWeight += $section['weight'];
                }
            }

            $evaluatorDetails[] = [
                'member_id' => $memberId,
                'evaluator_name' => $member ? "User #{$member->UserID}" : 'Unknown',
                'role' => $member ? $member->Role : 'Unknown',
                'section_scores' => $sectionScores,
                'total_weighted_score' => round($totalWeightedScore, 2),
            ];
        }

        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.evaluator-drilldown', [
            'tender' => $tender,
            'evaluatorDetails' => $evaluatorDetails,
        ]);
    }

    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.create');
    }

    /**
     * Persist consolidated scores snapshot or trigger any follow-up action
     */
    public function storeConsolidation($tenderId)
    {
        // For now, just recompute to validate and return success. Hook award workflow here if needed.
        $data = $this->getConsolidatedScores($tenderId);
        if (empty($data['supplierSummaries'])) {
            return back()->with('error', 'No evaluation data available to consolidate.');
        }

        return back()->with('success', 'Consolidated scores computed successfully.');
    }
}
