<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQCommittee;
use App\Models\Procurement\RFQCommitteeMember;
use App\Models\Procurement\RFQCriteria;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\RFQSupplierResponseEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RFQEvaluationController extends Controller
{
    public function index()
    {
        // Fetch RFQ evaluations from the database
        $rfqEvaluations = RFQEvaluation::with([
            'rfq',
            'evaluations.rfqEvaluation',
            'evaluations.rfqCriteria.section',
            'evaluations.rfqCriteriaUnscoped.weightedSection',
            'evaluations.supplier',
        ])->get();

        $evaluationsRanked = [];

        foreach ($rfqEvaluations as $evaluation) {
            $grouped = $evaluation->evaluations->groupBy('SupplierId');

            foreach ($grouped as $supplierId => $evalGroup) {
                $sectionGroups = $evalGroup->groupBy(fn($e) => $e->rfqCriteria?->section?->SectionName ?? 'Uncategorized');
                $grandWeightedTotal = 0;

                foreach ($sectionGroups as $section => $criteriaList) {
                    $first = $criteriaList->first();
                    // Read Weight from RFQSection relation; fallback to 0 if missing
                    $sectionWeight = (float) ($first->rfqCriteria?->weightedSection?->Weight ?? 0);
                    $maxScorePerCriteria = 10;
                    $maxTotal = $criteriaList->count() * $maxScorePerCriteria;
                    $actualTotal = $criteriaList->sum('Score');

                    if ($maxTotal > 0) {
                        $grandWeightedTotal += round(($actualTotal / $maxTotal) * $sectionWeight, 2);
                    }
                }

                $evaluationsRanked[] = [
                    'evaluation' => $evaluation,
                    'supplier' => $evalGroup->first()->supplier,
                    'supplierId' => $supplierId,
                    'rfq' => $evaluation->rfq,
                    'weightedTotal' => $grandWeightedTotal,
                    'response' => RFQResponse::where('SupplierId', $supplierId)
                        ->where('RFQId', $evaluation->RFQId)
                        ->first(),
                ];
            }
        }

        // Group by RFQId and rank within each group
        $groupedByRFQ = collect($evaluationsRanked)->groupBy('rfq.id');

        $finalRanked = [];
        foreach ($groupedByRFQ as $rfqId => $evaluations) {
            // Sort by weightedTotal in descending order within each RFQ
            $sorted = $evaluations->sortByDesc('weightedTotal')->values();

            // Assign rank within the current RFQ group
            foreach ($sorted as $rank => $entry) {
                $entry['rank'] = $rank + 1;
                $finalRanked[] = $entry;
            }
        }

        return view('procurement.rfqevaluation.index', ['rfqEvaluations' => $rfqEvaluations, 'evaluationsRanked' => $finalRanked]);
    }

    public function create()
    {
        // Load RFQs with sections (from t_Sections) and their criteria (from t_Criterias)
        $rfqs = RFQ::with([
            'sections.section.criteria',
            'rfqResponses.supplier',
            'committeeMembers.user.employee'
        ])->whereHas('rfqResponses')->get();

        $currencies = config('app.currencies');

        return view('procurement.rfqevaluation.create', compact('rfqs', 'currencies'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'CommitteeMember' => 'required|string',
            'UserID' => 'required|string',
            'RFQId' => 'required|integer|exists:t_RFQ,Id',
            'RFQComments' => 'nullable|string',
            'Confirmation' => 'required|boolean',
            'Evaluations' => 'required|array',
        ]);

        // Check for existing evaluation
        $existingEvaluation = RFQEvaluation::where('RFQId', $validated['RFQId'])
            ->where('UserCode', $validated['UserID'])
            ->first();

        if ($existingEvaluation) {
            return back()->with('error', 'You have already submitted an evaluation for this RFQ. Please review the existing evaluation.');
        }

        DB::beginTransaction();
        try {
            // Create main RFQ Evaluation record
            $rfqEval = RFQEvaluation::create([
                'CommitteeMemberName' => $validated['CommitteeMember'],
                'UserCode' => $validated['UserID'],
                'RFQId' => $validated['RFQId'],
                'RFQComment' => $validated['RFQComments'],
                'Confirmation' => $validated['Confirmation'],
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
            ]);

            foreach ($request->Evaluations as $supplierId => $criteriaSet) {
                foreach ($criteriaSet as $criteriaId => $scoreData) {
                    if ($criteriaId === 'SupplierId' || !is_array($scoreData)) {
                        continue;
                    }

                    $score = (int)$scoreData['Score'];

                    // Enforce max score of 10 and min score of 1
                    if ($score < 1 || $score > 10) {
                        throw new \Exception("Score for Supplier ID $supplierId and Criteria ID $criteriaId must be between 1 and 10.");
                    }

                    RFQSupplierResponseEvaluation::create([
                        'RFQEvaluationId' => $rfqEval->Id,
                        'SupplierId' => (int)$supplierId,
                        'CriteriaId' => (int)$criteriaId,
                        'Score' => $score,
                        'Comments' => $scoreData['Comments'] ?? null,
                        'CreatedBy' => auth()->id(),
                        'ModifiedBy' => auth()->id(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('evaluations.index')->with('success', 'Evaluation submitted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Error saving evaluation: ' . $th->getMessage());
        }
    }

    public function getRFQResponses($rfqId)
    {
        $rfqResponses = RFQResponse::where('RFQId', $rfqId)
            ->with('supplier', 'items.uom')
            ->get();

        // Fetch criteria by section
        $criteriaRows = RFQCriteria::with('criteria', 'section')
            ->where('RFQID', $rfqId)
            ->get();

        // Map section weights from t_RFQSection for this RFQ
        $weightsBySection = DB::table('t_RFQSection')
            ->where('RFQID', $rfqId)
            ->pluck('Weight', 'SectionID');

        // Attach a pseudo relation `weighted_section` to each criteria row for serialization
        $criteriaRows->each(function ($row) use ($weightsBySection) {
            $weight = (float) ($weightsBySection[$row->SectionID] ?? 0);
            $row->setRelation('weighted_section', ['Weight' => $weight]);
        });

        $criteria = $criteriaRows->groupBy('SectionID');

        return response()->json([
            'responses' => $rfqResponses,
            'criteria' => $criteria
        ]);
    }

    public function getCommitteeMemberInfo($rfqId)
    {
        $rfq = RFQ::find($rfqId);

        if (!$rfq) {
            return response()->json(['error' => 'RFQ not found'], 404);
        }

        $employeeId = optional(auth()->user())->EmployeeId ?? optional(auth()->user()?->employee)->Id;

        $member = RFQCommitteeMember::with('user.employee')
            ->where('RFQID', $rfq->Id)
            ->where('UserID', $employeeId)
            ->where('Response', 1)
            ->first();

        if (!$member) {
            return response()->json(['error' => 'User not part of committee or has not accepted the appointment']);
        }

        return response()->json([
            'CommitteeMember' => $member->user->Name,
            'UserID' => $member->UserID
        ]);
    }
}
