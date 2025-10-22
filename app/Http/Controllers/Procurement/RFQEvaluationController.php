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
            // Use unscoped relation consistently and eager-load nested relations for names/sections
            'evaluations.rfqCriteriaUnscoped.criteria',
            'evaluations.rfqCriteriaUnscoped.section',
            'evaluations.supplier.thirdParty',
        ])->get();

        $evaluationsRanked = [];

        // Build a map of RFQID => [SectionID => Weight] for all RFQs present
        $rfqIds = $rfqEvaluations->pluck('RFQId')->unique()->values();
        $rfqSectionWeights = DB::table('t_RFQSection')
            ->whereIn('RFQID', $rfqIds)
            ->get()
            ->groupBy('RFQID')
            ->map(function ($rows) {
                return $rows->pluck('Weight', 'SectionID')->map(fn($w) => (float)$w)->toArray();
            })->toArray();

        foreach ($rfqEvaluations as $evaluation) {
            $grouped = $evaluation->evaluations->groupBy('SupplierId');

            foreach ($grouped as $supplierId => $evalGroup) {
                $sectionGroups = $evalGroup->groupBy(fn($e) => $e->rfqCriteria?->section?->SectionName ?? 'Uncategorized');
                $grandWeightedTotal = 0;

                foreach ($sectionGroups as $section => $criteriaList) {
                    $first = $criteriaList->first();
                    // Determine SectionID and read weight from preloaded map; fallback to 0
                    $sectionId = $first->rfqCriteria?->SectionID ?? $first->rfqCriteriaUnscoped?->SectionID;
                    $weightsForRfq = $rfqSectionWeights[$evaluation->RFQId] ?? [];
                    $sectionWeight = (float)($sectionId ? ($weightsForRfq[$sectionId] ?? 0) : 0);
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

        return view('procurement.rfqevaluation.index', [
            'rfqEvaluations' => $rfqEvaluations,
            'evaluationsRanked' => $finalRanked,
            'rfqSectionWeights' => $rfqSectionWeights,
        ]);
    }

    public function consolidated($rfqId, Request $request)
    {
        // Allow rfq override from query string (for embedded selector)
        $rfqId = (int)($request->input('rfq', $rfqId));

        // Build list of RFQs that have evaluations (for RFQ picker)
        $evaluatedRfqs = RFQEvaluation::select('RFQId')
            ->distinct()
            ->with('rfq')
            ->get()
            ->filter(fn($e) => !is_null($e->rfq))
            ->map(fn($e) => ['Id' => $e->RFQId, 'RFQNumber' => $e->rfq->RFQNumber])
            ->values();

        // Default RFQ if invalid or missing
        if (!$rfqId || !$evaluatedRfqs->pluck('Id')->contains((int)$rfqId)) {
            $rfqId = $evaluatedRfqs->first()['Id'] ?? $rfqId;
        }

        // Load all evaluations for RFQ with nested data
        $rfqEvaluations = RFQEvaluation::with([
            'rfq',
            'evaluations.rfqEvaluation',
            'evaluations.rfqCriteriaUnscoped.criteria',
            'evaluations.rfqCriteriaUnscoped.section',
            'evaluations.supplier.thirdParty',
        ])->where('RFQId', $rfqId)->get();

        // Build section weights
        $rfqSectionWeights = DB::table('t_RFQSection')
            ->where('RFQID', $rfqId)
            ->get()
            ->pluck('Weight', 'SectionID')
            ->map(fn($w) => (float)$w)
            ->toArray();

        // Compute aggregates
        $supplierSectionScores = []; // [supplierId][sectionId] => [sumWeightedAcrossEvaluators, evaluatorCount]
        $supplierTotals = []; // [supplierId] => [sumWeightedAcrossEvaluators, evaluatorCount]
        $criteriaAverages = []; // [criteriaId] => [sumScores, evaluatorCount]
        $supplierCriteriaAverages = []; // [supplierId][criteriaId] => [sumScores, evaluatorCount]

        foreach ($rfqEvaluations as $evaluation) {
            $bySupplier = $evaluation->evaluations->groupBy('SupplierId');
            foreach ($bySupplier as $supplierId => $entries) {
                $sectionGroups = $entries->groupBy(fn($e) => $e->rfqCriteriaUnscoped?->SectionID);

                $evaluatorWeightedTotal = 0;
                foreach ($sectionGroups as $sectionId => $criteriaList) {
                    $weight = (float)($rfqSectionWeights[$sectionId] ?? 0);
                    $maxTotal = $criteriaList->count() * 10;
                    $actualTotal = $criteriaList->sum('Score');
                    $sectionWeighted = $maxTotal > 0 ? (($actualTotal / $maxTotal) * $weight) : 0;

                    $supplierSectionScores[$supplierId][$sectionId]['sum'] = ($supplierSectionScores[$supplierId][$sectionId]['sum'] ?? 0) + $sectionWeighted;
                    $supplierSectionScores[$supplierId][$sectionId]['count'] = ($supplierSectionScores[$supplierId][$sectionId]['count'] ?? 0) + 1;

                    $evaluatorWeightedTotal += $sectionWeighted;

                    // criteria averages per supplier and global
                    foreach ($criteriaList as $entry) {
                        $critId = $entry->CriteriaId;
                        $criteriaAverages[$critId]['sum'] = ($criteriaAverages[$critId]['sum'] ?? 0) + $entry->Score;
                        $criteriaAverages[$critId]['count'] = ($criteriaAverages[$critId]['count'] ?? 0) + 1;

                        $supplierCriteriaAverages[$supplierId][$critId]['sum'] = ($supplierCriteriaAverages[$supplierId][$critId]['sum'] ?? 0) + $entry->Score;
                        $supplierCriteriaAverages[$supplierId][$critId]['count'] = ($supplierCriteriaAverages[$supplierId][$critId]['count'] ?? 0) + 1;
                    }
                }

                $supplierTotals[$supplierId]['sum'] = ($supplierTotals[$supplierId]['sum'] ?? 0) + $evaluatorWeightedTotal;
                $supplierTotals[$supplierId]['count'] = ($supplierTotals[$supplierId]['count'] ?? 0) + 1;
            }
        }

        // Build presentation arrays
        $evaluatorCount = $rfqEvaluations->pluck('UserCode')->unique()->count();
        $suppliers = RFQResponse::where('RFQId', $rfqId)
            ->with('supplier.thirdParty')
            ->get()
            ->keyBy('SupplierId');

        // Build ordered sections with criteria list
        $rfqSections = DB::table('t_RFQSection')
            ->where('RFQID', $rfqId)
            ->orderBy('SectionID')
            ->get();
        $sections = DB::table('t_Sections')
            ->whereIn('Id', array_keys($rfqSectionWeights))
            ->get()
            ->keyBy('Id');
        $criteriaRows = RFQCriteria::with('criteria', 'section')
            ->where('RFQID', $rfqId)
            ->get()
            ->groupBy('SectionID');
        $sectionColumns = [];
        foreach ($rfqSections as $rfqSec) {
            $secId = $rfqSec->SectionID;
            $critList = ($criteriaRows[$secId] ?? collect())->values();
            $sectionColumns[] = [
                'id' => $secId,
                'name' => $sections[$secId]->SectionName ?? 'Section',
                'weight' => (float)($rfqSectionWeights[$secId] ?? 0),
                'criteria' => $critList->map(fn($row) => [
                    'id' => $row->CriteriaID,
                    'name' => $row->criteria->CriteriaName ?? 'Criteria',
                ])->values()->all(),
            ];
        }

        $supplierSummaries = [];
        foreach ($supplierTotals as $supplierId => $agg) {
            $avgTotal = $agg['count'] > 0 ? round($agg['sum'] / $agg['count'], 2) : 0;
            $sectionBreakdown = [];
            foreach (($supplierSectionScores[$supplierId] ?? []) as $sectionId => $secAgg) {
                $sectionBreakdown[] = [
                    'section_id' => $sectionId,
                    'section_name' => $sections[$sectionId]->SectionName ?? 'Section',
                    'weight' => $rfqSectionWeights[$sectionId] ?? 0,
                    'score' => $secAgg['count'] > 0 ? round($secAgg['sum'] / $secAgg['count'], 2) : 0,
                ];
            }

            $supplierSummaries[] = [
                'supplier_id' => $supplierId,
                'supplier_name' => ($suppliers[$supplierId]->supplier->thirdParty->ThirdPartyName ?? $suppliers[$supplierId]->supplier->thirdParty->TradingName ?? $suppliers[$supplierId]->SupplierName ?? 'N/A'),
                'total_weighted_average' => $avgTotal,
                'section_scores' => $sectionBreakdown,
            ];
        }

        // Criteria averages list and per supplier averages map
        $criteriaSummary = [];
        $supplierCriterionAvgScores = [];
        if (!empty($criteriaAverages)) {
            $criteriaMeta = RFQCriteria::with('criteria')
                ->where('RFQID', $rfqId)
                ->get()
                ->keyBy('CriteriaID');
            foreach ($criteriaAverages as $critId => $agg) {
                $criteriaSummary[] = [
                    'criteria_id' => $critId,
                    'criteria_name' => $criteriaMeta[$critId]->criteria->CriteriaName ?? 'Criteria',
                    'average_score_out_of_10' => $agg['count'] > 0 ? round($agg['sum'] / $agg['count'], 2) : 0,
                ];
            }

            foreach ($supplierCriteriaAverages as $supplierId => $critAggs) {
                foreach ($critAggs as $critId => $agg) {
                    $supplierCriterionAvgScores[$supplierId][$critId] = $agg['count'] > 0 ? round($agg['sum'] / $agg['count'], 1) : 0;
                }
            }
        }

        // Current award if any
        $award = \App\Models\Procurement\RFQAward::where('RFQId', $rfqId)->first();

        // Rank and recommendation
        $supplierSummaries = collect($supplierSummaries)
            ->sortByDesc('total_weighted_average')
            ->values()
            ->map(function ($row, $idx) {
                $rank = $idx + 1;
                $rec = [
                    'status' => $rank === 1 ? 'Recommended' : ($rank === 2 ? 'Backup' : 'Not Recommended'),
                    'class' => $rank === 1 ? 'bg-success' : ($rank === 2 ? 'bg-secondary' : 'bg-danger'),
                ];
                $row['rank'] = $rank;
                $row['recommendation'] = $rec;
                return $row;
            })->all();

        $viewData = [
            'rfqId' => $rfqId,
            'rfq' => $rfqEvaluations->first()->rfq ?? null,
            'supplierSummaries' => $supplierSummaries,
            'criteriaSummary' => $criteriaSummary,
            'sections' => collect($sections->all())->map(fn($s) => ['id' => $s->Id, 'name' => $s->SectionName, 'weight' => $rfqSectionWeights[$s->Id] ?? 0])->values(),
            'award' => $award,
            'evaluatedRfqs' => $evaluatedRfqs,
            'evaluatorCount' => $evaluatorCount,
            'sectionColumns' => $sectionColumns,
            'supplierCriterionAvgScores' => $supplierCriterionAvgScores,
        ];

        if ($request->boolean('embed')) {
            return view('procurement.rfqevaluation.consolidated_embed', $viewData);
        }

        return view('procurement.rfqevaluation.consolidated', $viewData);
    }

    public function awardSupplier($rfqId, $supplierId, Request $request)
    {
        $request->validate([
            'Comments' => 'nullable|string',
        ]);

        $award = \App\Models\Procurement\RFQAward::updateOrCreate(
            ['RFQId' => (int)$rfqId],
            [
                'SupplierId' => (int)$supplierId,
                'Comments' => $request->input('Comments'),
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]
        );

        // Notify supplier portal API
        try {
            $payload = [
                'rfqId' => (int)$rfqId,
                'supplierId' => (int)$supplierId,
                'status' => 'Awarded',
                'awardedOn' => now()->toISOString(),
                'comments' => $request->input('Comments'),
            ];

            $endpoint = config('services.procurement_supplier_portal.endpoint', 'http://localhost:3000/api/procurement/rfq-suppliers');
            $apiKey = config('services.procurement_supplier_portal.key');

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-API-Key' => $apiKey,
            ])->post($endpoint, $payload);

            if (!$response->successful()) {
                \Log::warning('Supplier award notify failed', ['rfqId' => $rfqId, 'supplierId' => $supplierId, 'status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            \Log::error('Supplier award notify exception', ['rfqId' => $rfqId, 'supplierId' => $supplierId, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Award saved and supplier notified.');
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
            ->with('supplier.thirdParty', 'items.uom')
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
            $weight = (float)($weightsBySection[$row->SectionID] ?? 0);
            $row->setRelation('weighted_section', ['Weight' => $weight]);
        });

        // Group by SectionID and reindex each group to a plain array for clean JSON
        $criteria = $criteriaRows
            ->groupBy('SectionID')
            ->map(function ($group) {
                return $group->values();
            });

        // Provide a simple map of SectionID => Weight as well for the frontend
        $sectionWeights = collect($weightsBySection)->map(function ($w) {
            return (float)$w;
        })->toArray();

        return response()->json([
            'responses' => $rfqResponses,
            'criteria' => $criteria,
            'sectionWeights' => $sectionWeights,
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
