<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationCriteria;
use App\Models\Procurement\Prequalification\PrequalificationEvaluation;
use App\Models\Procurement\Prequalification\PrequalificationResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParies\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PrequalificationEvaluationController extends Controller
{
    public function index(): View
    {
        // Initial page load; DataTables will fetch via ajax (future endpoint) or we can feed minimal set.
        return view('procurement.suppliers.prequalification.prequalification-evaluation.index');
    }

    /**
     * DataTables JSON endpoint for evaluations (passed / failed via status param)
     */
    public function datatable(Request $request)
    {
        try {
            $statusFilter = $request->get('status'); // passed | failed
            $userId = Auth::id();

            $appIds = PrequalificationEvaluation::where('EvaluatorID', $userId)
                ->distinct()->pluck('ApplicationID');

            if ($appIds->isEmpty()) {
                return response()->json(['data'=>[]]);
            }

            $appsQuery = PrequalificationApplication::with(['result','supplier'])
                ->whereIn('ApplicationID', $appIds);

            if (in_array($statusFilter,['passed','failed'])) {
                $appsQuery->whereHas('result', function($q) use ($statusFilter){
                    $q->where('Decision', $statusFilter === 'passed' ? 'Passed' : 'Failed');
                });
            }

            $applications = $appsQuery->orderByDesc('SubmittedOn')->limit(500)->get();

            // Load supplier rows (t_Suppliers) for these (ThirdPartyID + RoundID)
            $supplierRows = \App\Models\ThirdParies\Supplier::whereIn('ThirdPartyID', $applications->pluck('SupplierID')->filter())
                ->whereIn('RoundID', $applications->pluck('RoundID')->filter())
                ->get()
                ->groupBy(function($s){ return $s->ThirdPartyID.'-'.$s->RoundID; });

            $data = $applications->map(function($app) use ($supplierRows){
                $res = $app->result;
                $key = $app->SupplierID.'-'.$app->RoundID;
                $supplierRow = $supplierRows->get($key)?->first();
                
                // Check if supplier is prequalified for this specific category
                $categoryPrequalified = DB::table('t_PrequalificationRoundSupplierCategory')
                    ->where('RoundID', $app->RoundID)
                    ->where('ThirdPartyID', $app->SupplierID)
                    ->where('SupplierCategoryID', $app->CategoryID)
                    ->exists();
                
                $supplierActive = (bool) ($supplierRow?->Active_Status);   // flag on t_Suppliers
                $decision = $res?->Decision;
                
                // Hide prequalify button if:
                // 1. Supplier already prequalified for this specific category OR
                // 2. There's a result with "Passed" decision (evaluation completed)
                $prequalifyAllowed = !($categoryPrequalified || $decision === 'Passed');
                
                return [
                    'application_no' => $app->applicationNo,
                    'supplier' => $app->supplier?->ThirdPartyName,
                    'status' => $app->Status,
                    'submitted_on' => optional($app->SubmittedOn)->format('Y-m-d'),
                    'total_score' => $res ? number_format($res->TotalScore, 2) : null,
                    'decision' => $decision,
                    'application_id' => $app->ApplicationID,
                    'supplier_id' => $app->SupplierID,
                    'round_id' => $app->RoundID,
                    'category_id' => $app->CategoryID,
                    'is_prequalified' => !$prequalifyAllowed, // kept for backward compatibility but now means 'button hidden'
                    'category_prequalified' => $categoryPrequalified,
                    'supplier_active' => $supplierActive,
                    'prequalify_allowed' => $prequalifyAllowed,
                ];
            });

            return response()->json(['data'=>$data]);
        } catch (\Throwable $e) {
            Log::error('Prequalification datatable error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return response()->json(['data'=>[], 'error'=>'Failed to load data'], 200);
        }
    }

    /**
     * Bulk prequalify suppliers for a round (Decision == Passed)
     */
    public function bulkPrequalify(int $roundId): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $now = Carbon::now();
    $userId = Auth::id();
        // Get applications with passed decision
        $passedApps = PrequalificationApplication::with('result')
            ->where('RoundID', $roundId)
            ->whereHas('result', fn($q)=> $q->where('Decision','Passed'))
            ->get();

        if ($passedApps->isEmpty()) {
            if (request()->expectsJson()) {
                return response()->json(['status'=>'warning','message'=>'No passed applications to prequalify.']);
            }
            return back()->with('warning','No passed applications to prequalify.');
        }

        DB::transaction(function () use ($passedApps, $roundId, $now, $userId) {
            foreach ($passedApps as $app) {
                // Create category-specific prequalification record
                DB::table('t_PrequalificationRoundSupplierCategory')->updateOrInsert(
                    [
                        'RoundID' => $roundId,
                        'ThirdPartyID' => $app->SupplierID,
                        'SupplierCategoryID' => $app->CategoryID,
                    ],
                    [
                        'CreatedOn' => $now,
                        'ModifiedOn' => $now,
                    ]
                );
                
                // mark third party as prequalified
                ThirdParties::where('Id', $app->SupplierID)->update([
                    'IsPrequalified'=>1,
                    'ModifiedOn'=>$now,
                    'ModifiedBy'=>$userId,
                ]);
                // ensure supplier row exists with required audit fields
                $supplier = Supplier::firstOrCreate(
                    ['ThirdPartyID'=>$app->SupplierID, 'RoundID'=>$roundId],
                    [
                        'Active_Status'=>1,
                        'CreatedOn'=>$now,
                        'CreatedBy'=>$userId,
                    ]
                );
                $supplier->Active_Status = 1;
                $supplier->ModifiedOn = $now;
                $supplier->ModifiedBy = $userId;
                $supplier->save();
            }
        });

        if (request()->expectsJson()) {
            return response()->json(['status'=>'success','message'=>'Suppliers prequalified successfully for this round.','count'=>$passedApps->count()]);
        }
        return back()->with('success','Suppliers prequalified successfully for this round.');
    }

    /**
     * Individually prequalify a supplier even if failed (under review scenario)
     */
    public function prequalifySupplier(int $thirdPartyId, int $roundId, int $categoryId): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $now = Carbon::now();
        $userId = Auth::id();
        DB::transaction(function () use ($thirdPartyId, $roundId, $categoryId, $now, $userId) {
            // Create category-specific prequalification record
            DB::table('t_PrequalificationRoundSupplierCategory')->updateOrInsert(
                [
                    'RoundID' => $roundId,
                    'ThirdPartyID' => $thirdPartyId,
                    'SupplierCategoryID' => $categoryId,
                ],
                [
                    'CreatedOn' => $now,
                    'ModifiedOn' => $now,
                ]
            );
            
            // Also update the global prequalification flag for backward compatibility
            ThirdParties::where('Id',$thirdPartyId)->update([
                'IsPrequalified'=>1,
                'ModifiedOn'=>$now,
                'ModifiedBy'=>$userId,
            ]);
            
            // Ensure supplier row exists with required audit fields
            Supplier::updateOrCreate(
                ['ThirdPartyID'=>$thirdPartyId,'RoundID'=>$roundId],
                [
                    'Active_Status'=>1,
                    'ModifiedOn'=>$now,
                    'CreatedOn'=>$now,
                    'CreatedBy'=>$userId,
                    'ModifiedBy'=>$userId,
                ]
            );
        });
        if (request()->expectsJson()) {
            return response()->json(['status'=>'success','message'=>'Supplier prequalified for this category.']);
        }
        return back()->with('success','Supplier prequalified for this category.');
    }

    /**
     * Expire suppliers for rounds whose EndDate has elapsed.
     */
    public function expireRounds(): RedirectResponse
    {
        $now = Carbon::today();
        $userId = Auth::id();
        $expiredRoundIds = \App\Models\Procurement\Prequalification\PrequalificationRound::where('EndDate','<',$now)->pluck('RoundID');
        if ($expiredRoundIds->isEmpty()) {
            return back()->with('info','No rounds to expire.');
        }
        Supplier::whereIn('RoundID',$expiredRoundIds)->update([
            'Active_Status'=>0,
            'ModifiedOn'=>$now,
            'ModifiedBy'=>$userId,
        ]);
        return back()->with('success','Expired round suppliers deactivated.');
    }

    public function showEvaluationForm($applicationId): View|RedirectResponse
    {
        $application = PrequalificationApplication::with('supplier', 'category')->findOrFail($applicationId);
        $round = $application->round;

        if (!$round) {
            return redirect()->back()->with('error', 'The prequalification round for this application could not be found.');
        }

        $evaluatorId = Auth::id();

        $sections = $round->prequalificationSections()
            ->with(['masterSection', 'criteria.masterCriteria'])
            ->get();

        $existingEvaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->where('EvaluatorID', $evaluatorId)
            ->get()
            ->keyBy('CriteriaID');

        // Check if there's already a result for this application
        $result = $application->result;
        $isReadonly = $result !== null; // Make readonly if result already exists
        
        // Check if supplier is already prequalified
        $isPrequalified = $application->supplier?->IsPrequalified ?? false;

        return view('procurement.suppliers.prequalification.prequalification-evaluation.evaluate', compact('application', 'sections', 'existingEvaluations', 'isReadonly', 'isPrequalified', 'result'));
    }

    public function submitEvaluation(Request $request, $applicationId): RedirectResponse
    {
        $application = PrequalificationApplication::findOrFail($applicationId);
        
        // Check if there's already a result for this application
        $result = $application->result;
        if ($result !== null) {
            return redirect()->back()->with('error', 'This evaluation has already been completed and cannot be modified.');
        }
        
        $evaluatorId = Auth::id();

        $request->validate([
            'criteria_scores' => 'required|array',
            'criteria_scores.*.criteria_id' => 'required|integer',
            'criteria_scores.*.score' => 'nullable|numeric|min:0|max:10',
            'criteria_scores.*.max_score' => 'required|numeric|in:10',
            'general_comments' => 'nullable|string',
        ]);

        foreach ($request->input('criteria_scores') as $evaluationData) {
            $criteriaId = $evaluationData['criteria_id'];
            $maxScore = $evaluationData['max_score'];
            $scoreAwarded = $evaluationData['score'];

            $prequalificationCriteria = PrequalificationCriteria::where('CriteriaId', $criteriaId)
                ->first();

            if (!$prequalificationCriteria) {
                continue;
            }

            $sectionId = $prequalificationCriteria->SectionId;

            if (!is_null($scoreAwarded) && $scoreAwarded > $maxScore) {
                throw ValidationException::withMessages([
                    "criteria_scores.{$criteriaId}.score" => "Score awarded cannot exceed the max score of {$maxScore}."
                ]);
            }

            PrequalificationEvaluation::updateOrCreate(
                [
                    'ApplicationID' => $applicationId,
                    'EvaluatorID' => $evaluatorId,
                    'CriteriaID' => $criteriaId,
                ],
                [
                    'SectionID' => $sectionId,
                    'Score' => $scoreAwarded,
                    'MaxScore' => $maxScore,
                ]
            );
        }

        $application = PrequalificationApplication::with('round.prequalificationSections.criteria')->find($applicationId);
        // Recompute and persist results immediately so decision reflects latest scores
        $evaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->get();
        if ($application && $application->round && $evaluations->isNotEmpty()) {
            $preqSections = $application->round->prequalificationSections->keyBy('SectionId');
            $totalSectionWeight = max(0.0, (float) ($preqSections->sum('Weight') ?? 0));
            $weightScale = ($totalSectionWeight > 0 && abs($totalSectionWeight - 100.0) > 0.0001)
                ? (100.0 / $totalSectionWeight)
                : 1.0;

            $grandTotal = 0.0;
            foreach ($evaluations->groupBy('SectionID') as $sectionId => $sectionEvaluations) {
                $sectionModel = $preqSections[$sectionId] ?? null;
                $sectionWeight = ($sectionModel?->Weight ?? 0) * $weightScale;
                $criteriaCount = $sectionModel?->criteria?->count() ?: max(1, $sectionEvaluations->count());
                $perCriterionWeight = $criteriaCount > 0 ? ($sectionWeight / $criteriaCount) : 0;

                $sectionTotal = 0.0;
                foreach ($sectionEvaluations as $eval) {
                    $raw = (float) ($eval->Score ?? 0);
                    if ($raw < 0) $raw = 0; if ($raw > 10) $raw = 10;
                    $sectionTotal += $perCriterionWeight * ($raw / 10);
                }
                $grandTotal += round($sectionTotal, 6);
            }
            $grandTotal = round($grandTotal, 2);
            $threshold = (int) config('prequalification.passing_threshold', 60);
            $decision = ($grandTotal >= $threshold) ? 'Passed' : 'Failed';

            PrequalificationResult::updateOrCreate(
                ['ApplicationID' => $application->ApplicationID],
                [
                    'TotalScore' => $grandTotal,
                    'Decision' => $decision,
                    'ApprovalBy' => Auth::id(),
                ]
            );
        }
        if ($request->filled('general_comments')) {
            $application->GeneralComments = $request->input('general_comments');
            $application->save();
        }

        return redirect()->route('prequalification.applications.show', $applicationId)
            ->with('success', 'Evaluation submitted successfully!');
    }
}
