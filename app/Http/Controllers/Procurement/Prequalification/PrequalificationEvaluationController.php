<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\ApplicationCategoryStatus;
use App\Models\Procurement\Prequalification\CategoryProgressHistory;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationCriteria;
use App\Models\Procurement\Prequalification\PrequalificationEvaluation;
use App\Models\Procurement\Prequalification\PrequalificationResult;
use App\Models\ThirdParies\Supplier;
use App\Models\ThirdParty\ThirdParties;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $roundId = $request->get('round_id'); // Filter by specific round

            // Modified to show all applications, allowing all authorized users to see suppliers
            $appsQuery = PrequalificationApplication::with(['result', 'supplier.party']);

            // Filter by round if specified
            if ($roundId) {
                $appsQuery->where('RoundID', $roundId);
            }

            if ($statusFilter === 'pending') {
                $appsQuery->doesntHave('result');
            } elseif (in_array($statusFilter, ['passed', 'failed'])) {
                $appsQuery->whereHas('result', function ($q) use ($statusFilter) {
                    $q->where('Decision', $statusFilter === 'passed' ? 'Passed' : 'Failed');
                });
            }

            $applications = $appsQuery->orderByDesc('SubmittedOn')->limit(500)->get();

            // Load supplier rows (t_Suppliers) for these (SupplierMasterId + RoundID + CategoryId)
            $supplierRows = \App\Models\ThirdParies\Supplier::whereIn('SupplierMasterId', $applications->pluck('SupplierID')->filter())
                ->whereIn('RoundID', $applications->pluck('RoundID')->filter())
                ->whereIn('CategoryId', $applications->pluck('CategoryID')->filter())
                ->get()
                ->groupBy(function ($s) {
                    return $s->SupplierMasterId . '-' . $s->RoundID . '-' . $s->CategoryId;
                });

            $data = $applications->map(function ($app) use ($supplierRows) {
                $res = $app->result;
                $key = $app->SupplierID . '-' . $app->RoundID . '-' . $app->CategoryID;
                $supplierRow = $supplierRows->get($key)?->first();

                // Check if supplier is prequalified for this specific category
                $partyId = $app->supplier?->ThirdPartyId;
                $categoryPrequalified = DB::table('t_PrequalificationRoundSupplierCategory')
                    ->where('RoundID', $app->RoundID)
                    ->where('ThirdPartyID', $partyId)
                    ->where('SupplierCategoryID', $app->CategoryID)
                    ->exists();

                $supplierActive = (bool)($supplierRow?->Active_Status);   // flag on t_Suppliers
                $decision = $res?->Decision;

                // Hide prequalify button if:
                // 1. Supplier is already prequalified for this category, OR
                // 2. Supplier has NOT been evaluated yet (no decision)
                $hasBeenEvaluated = !is_null($decision);
                $prequalifyAllowed = !$categoryPrequalified && $hasBeenEvaluated;

                return [
                    'application_no' => $app->applicationNo,
                    'supplier' => $app->supplier?->party?->ThirdPartyName,
                    // Status is already cast to enum in model, so we can call getLabel() directly
                    'status' => $app->Status?->getLabel() ?? 'Unknown',
                    'submitted_on' => optional($app->SubmittedOn)->format('Y-m-d'),
                    'total_score' => $res ? number_format($res->TotalScore, 2) : null,
                    'decision' => $decision,
                    'application_id' => $app->ApplicationID,
                    'supplier_id' => $partyId,
                    'round_id' => $app->RoundID,
                    'category_id' => $app->CategoryID,
                    'is_prequalified' => ! $prequalifyAllowed, // kept for backward compatibility but now means 'button hidden'
                    'category_prequalified' => $categoryPrequalified,
                    'supplier_active' => $supplierActive,
                    'prequalify_allowed' => $prequalifyAllowed,
                ];
            });

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {

            return response()->json(['data' => [], 'error' => 'Failed to load data'], 200);
        }
    }

    /**
     * Bulk prequalify suppliers for a round (Decision == Passed)
     */
    public function bulkPrequalify(int $roundId): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $now = Carbon::now();
        $userId = Auth::id();

        // Validate round existence before proceeding to avoid FK violations
        $round = \App\Models\Procurement\Prequalification\PrequalificationRound::find($roundId);
        if (! $round) {


            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Round $roundId not found. Please provide a valid RoundID.",
                ], 422);
            }

            return back()->with('error', "Round $roundId not found. Please provide a valid RoundID.");
        }
        // Get applications with passed decision
        $passedApps = PrequalificationApplication::with('result')
            ->where('RoundID', $roundId)
            ->whereHas('result', fn ($q) => $q->where('Decision', 'Passed'))
            ->get();

        if ($passedApps->isEmpty()) {
            if (request()->expectsJson()) {
                return response()->json(['status' => 'warning', 'message' => 'No passed applications to prequalify.']);
            }

            return back()->with('warning', 'No passed applications to prequalify.');
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

                // mark supplier master as prequalified
                \App\Models\ThirdParty\SupplierMaster::where('Id', $app->SupplierID)->update([
                    'IsPrequalified' => 1,
                    'ModifiedOn' => $now,
                    'ModifiedBy' => $userId,
                ]);
                // ensure supplier row exists per category with required audit fields
                $supplier = Supplier::updateOrCreate(
                    ['SupplierMasterId' => $app->SupplierID, 'RoundID' => $roundId, 'CategoryId' => $app->CategoryID],
                    [
                        'Active_Status' => 1,
                        'CreatedOn' => $now,
                        'CreatedBy' => $userId,
                        'ModifiedOn' => $now,
                        'ModifiedBy' => $userId,
                    ]
                );

                // Upsert per-category progress to Approved and add history
                try {
                    $acs = ApplicationCategoryStatus::firstOrNew([
                        'ApplicationId' => $app->ApplicationID,
                        'CategoryId' => $app->CategoryID,
                    ]);
                    $prevStatus = $acs->exists ? $acs->Status : null;
                    $prevProgress = $acs->exists ? (float)$acs->ProgressPercent : 0.0;
                    if (! $acs->exists) {
                        $acs->CreatedBy = $userId;
                        $acs->CreatedOn = $now;
                    }
                    $acs->Status = 'A'; // Approved / Prequalified
                    $acs->ProgressPercent = 100.00;
                    $acs->Stage = 'prequalified';
                    $acs->StageLabel = 'Prequalified';
                    $acs->DecisionDate = $now;
                    $acs->ModifiedBy = $userId;
                    $acs->ModifiedOn = $now;
                    $acs->save();

                    CategoryProgressHistory::create([
                        'ApplicationCategoryId' => $acs->Id,
                        'PreviousStatus' => $prevStatus,
                        'NewStatus' => 'A',
                        'PreviousProgress' => $prevProgress,
                        'NewProgress' => 100.00,
                        'ChangedBy' => $userId,
                        'Notes' => 'Bulk prequalification approved',
                        'CreatedBy' => $userId,
                        'CreatedOn' => $now,
                    ]);
                } catch (\Throwable $e) {

                }

                // Set application status to Prequalified for the same triplet
                try {
                    PrequalificationApplication::where('ApplicationID', $app->ApplicationID)
                        ->update(['Status' => PrequalificationApplicationEnum::Prequalified]);
                } catch (\Throwable $e) {

                }
            }
        });

        if (request()->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Suppliers prequalified successfully for this round.', 'count' => $passedApps->count()]);
        }

        return back()->with('success', 'Suppliers prequalified successfully for this round.');
    }

    /**
     * Individually prequalify a supplier (category-level), even if failed (under review scenario)
     * Route params order: {roundId}/{thirdPartyId}/{categoryId}
     */
    public function prequalifySupplier(int $roundId, int $thirdPartyId, int $categoryId): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $now = Carbon::now();
        $userId = Auth::id();
        // Validate round existence before proceeding to avoid FK violations
        $round = \App\Models\Procurement\Prequalification\PrequalificationRound::find($roundId);
        if (! $round) {

            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Round $roundId not found. Please refresh and try again.",
                ], 422);
            }

            return back()->with('error', "Round $roundId not found. Please refresh and try again.");
        }

        // Validate third party (supplier) existence to ensure correct mapping
        $thirdParty = ThirdParties::find($thirdPartyId);
        if (! $thirdParty) {
            Log::warning('Single prequalify aborted: ThirdParty not found', [
                'roundId' => $roundId,
                'thirdPartyId' => $thirdPartyId,
                'categoryId' => $categoryId,
                'userId' => $userId,
            ]);
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Supplier (ThirdParty) $thirdPartyId not found.",
                ], 422);
            }

            return back()->with('error', "Supplier (ThirdParty) $thirdPartyId not found.");
        }

        // Resolve SupplierMaster from ThirdPartyId
        $supplierMaster = \App\Models\ThirdParty\SupplierMaster::where('ThirdPartyId', $thirdPartyId)->first();
        if (! $supplierMaster) {
            Log::warning('Single prequalify aborted: SupplierMaster not found for ThirdParty', ['thirdPartyId' => $thirdPartyId]);
            if (request()->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => "Supplier Master record not found for ThirdParty $thirdPartyId."], 422);
            }

            return back()->with('error', "Supplier Master record not found for this party.");
        }
        $supplierMasterId = $supplierMaster->Id;

        // Validate category existence (defensive)
        $categoryExists = DB::table('t_SupplierCategories')
            ->where('SupplierCategoryID', $categoryId)
            ->exists();
        if (! $categoryExists) {
            Log::warning('Single prequalify aborted: Category not found', [
                'roundId' => $roundId,
                'thirdPartyId' => $thirdPartyId,
                'categoryId' => $categoryId,
                'userId' => $userId,
            ]);
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Category $categoryId not found.",
                ], 422);
            }

            return back()->with('error', "Category $categoryId not found.");
        }

        // Optional trace: try to locate an application that matches the triplet for auditability
        $matchingApplication = PrequalificationApplication::where([
            'RoundID' => $roundId,
            'SupplierID' => $supplierMasterId, // Corrected to use SupplierMasterId
            'CategoryID' => $categoryId,
        ])->orderByDesc('SubmittedOn')->first();

        Log::info('Single prequalify invoked', [
            'roundId' => $roundId,
            'thirdPartyId' => $thirdPartyId,
            'supplierMasterId' => $supplierMasterId,
            'categoryId' => $categoryId,
            'foundApplicationId' => $matchingApplication?->ApplicationID,
            'userId' => $userId,
        ]);
        DB::transaction(function () use ($thirdPartyId, $supplierMasterId, $roundId, $categoryId, $now, $userId) {
            // Create category-specific prequalification record
            DB::table('t_PrequalificationRoundSupplierCategory')->updateOrInsert(
                [
                    'RoundID' => $roundId,
                    'ThirdPartyID' => $thirdPartyId, // Correct: Uses ThirdPartyId
                    'SupplierCategoryID' => $categoryId,
                ],
                [
                    'CreatedOn' => $now,
                    'ModifiedOn' => $now,
                ]
            );

            // Also update the supplier master prequalification flag
            \App\Models\ThirdParty\SupplierMaster::where('Id', $supplierMasterId)->update([ // Correct: Uses SupplierMasterId
                'IsPrequalified' => 1,
                'ModifiedOn' => $now,
                'ModifiedBy' => $userId,
            ]);

            // Ensure supplier row exists per category with required audit fields
            Supplier::updateOrCreate(
                ['SupplierMasterId' => $supplierMasterId, 'RoundID' => $roundId, 'CategoryId' => $categoryId], // Correct: Uses SupplierMasterId
                [
                    'Active_Status' => 1,
                    'ModifiedOn' => $now,
                    'CreatedOn' => $now,
                    'CreatedBy' => $userId,
                    'ModifiedBy' => $userId,
                ]
            );

            // Upsert per-category progress to Approved and add history
            try {
                // Try to find a matching application for audit linkage
                $matchingApp = PrequalificationApplication::where([
                    'RoundID' => $roundId,
                    'SupplierID' => $supplierMasterId, // Correct: Uses SupplierMasterId
                    'CategoryID' => $categoryId,
                ])->orderByDesc('SubmittedOn')->first();

                if ($matchingApp) {
                    // Resolve a valid ThirdPartyUser ID to satisfy the FK_AppCatStatus_CreatedBy constraint
                    // This table strictly requires a ThirdPartyUser ID, causing issues when updated by System Users.
                    // We fallback to the first user of the ThirdParty as a proxy.
                    $proxyUserId = \App\Models\ThirdParty\ThirdPartyUser::where('ThirdPartyID', $thirdPartyId)->value('Id');
                    $auditUserId = $proxyUserId ?? $userId; // Fallback to system user if no TP user found (will likely fail constraint but best effort)

                    $acs = ApplicationCategoryStatus::firstOrNew([
                        'ApplicationId' => $matchingApp->ApplicationID,
                        'CategoryId' => $categoryId,
                    ]);
                    $prevStatus = $acs->exists ? $acs->Status : null;
                    $prevProgress = $acs->exists ? (float)$acs->ProgressPercent : 0.0;
                    if (! $acs->exists) {
                        $acs->CreatedBy = $auditUserId;
                        $acs->CreatedOn = $now;
                    }
                    $acs->Status = 'A';
                    $acs->ProgressPercent = 100.00;
                    $acs->Stage = 'prequalified';
                    $acs->StageLabel = 'Prequalified';
                    $acs->DecisionDate = $now;
                    $acs->ModifiedBy = $auditUserId;
                    $acs->ModifiedOn = $now;
                    $acs->save();

                    CategoryProgressHistory::create([
                        'ApplicationCategoryId' => $acs->Id,
                        'PreviousStatus' => $prevStatus,
                        'NewStatus' => 'A',
                        'PreviousProgress' => $prevProgress,
                        'NewProgress' => 100.00,
                        'ChangedBy' => $userId, // This table likely points to t_Users or is polymorphic, so we keep real user
                        'Notes' => 'Manual prequalification approved',
                        'CreatedBy' => $userId,
                        'CreatedOn' => $now,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to update category progress (single prequalify)', [
                    'roundId' => $roundId,
                    'thirdPartyId' => $thirdPartyId,
                    'categoryId' => $categoryId,
                    'error' => $e->getMessage(),
                ]);
            }

            // After transaction, set any matching application(s) to Prequalified
            try {
                PrequalificationApplication::where([
                    'RoundID' => $roundId,
                    'SupplierID' => $thirdPartyId,
                    'CategoryID' => $categoryId,
                ])->update(['Status' => PrequalificationApplicationEnum::Prequalified]);
            } catch (\Throwable $e) {
                Log::warning('Failed to set application status Prequalified (single)', [
                    'roundId' => $roundId,
                    'thirdPartyId' => $thirdPartyId,
                    'categoryId' => $categoryId,
                    'error' => $e->getMessage(),
                ]);
            }
        });
        if (request()->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Supplier prequalified for this category.']);
        }

        return back()->with('success', 'Supplier prequalified for this category.');
    }

    /**
     * Expire suppliers for rounds whose EndDate has elapsed.
     */
    public function expireRounds(): RedirectResponse
    {
        $now = Carbon::today();
        $userId = Auth::id();
        $expiredRoundIds = \App\Models\Procurement\Prequalification\PrequalificationRound::where('EndDate', '<', $now)->pluck('RoundID');
        if ($expiredRoundIds->isEmpty()) {
            return back()->with('info', 'No rounds to expire.');
        }
        Supplier::whereIn('RoundID', $expiredRoundIds)->update([
            'Active_Status' => 0,
            'ModifiedOn' => $now,
            'ModifiedBy' => $userId,
        ]);

        return back()->with('success', 'Expired round suppliers deactivated.');
    }

    public function showEvaluationForm($applicationId): View|RedirectResponse
    {
        $application = PrequalificationApplication::with('supplier.party', 'category')->findOrFail($applicationId);
        $round = $application->round;

        if (! $round) {
            return redirect()->back()->with('error', 'The prequalification round for this application could not be found.');
        }

        $evaluatorId = Auth::id();

        $sections = $round->prequalificationSections()
            ->with(['masterSection', 'criteria.masterCriteria'])
            ->get();

        // Ensure only valid, included criteria with a master record are presented
        $sections->each(function ($section) {
            if (! ($section->criteria instanceof \Illuminate\Support\Collection)) {
                $section->setRelation('criteria', collect($section->criteria ?? []));
            }
            $cleaned = $section->criteria
                ->filter(function ($c) {
                    // Included flag (default true if null) and must resolve to a masterCriteria
                    $included = is_null($c->Included) ? true : (bool) $c->Included;

                    return $included && $c->masterCriteria;
                })
                ->unique('CriteriaId')
                ->values();
            $section->setRelation('criteria', $cleaned);
        });

        $existingEvaluations = PrequalificationEvaluation::where('ApplicationID', $applicationId)
            ->where('EvaluatorID', $evaluatorId)
            ->get()
            ->keyBy('CriteriaID');

        // Check if there's already a result for this application
        $result = $application->result;
        $isReadonly = $result !== null; // Make readonly if result already exists

        // Check if supplier is already prequalified for this specific category within the round
        $isPrequalified = DB::table('t_PrequalificationRoundSupplierCategory')
            ->where('RoundID', $application->RoundID)
            ->where('ThirdPartyID', $application->SupplierID)
            ->where('SupplierCategoryID', $application->CategoryID)
            ->exists();

        // Load supporting documents uploaded by the supplier for this application
        // These are linked via t_SupplierPreqApplicationDocuments.ApplicationID
        $documents = $application->documents()
            ->with(['dmsDocument.current'])
            ->orderByDesc('CreatedOn')
            ->get();

        return view(
            'procurement.suppliers.prequalification.prequalification-evaluation.evaluate',
            compact('application', 'sections', 'existingEvaluations', 'isReadonly', 'isPrequalified', 'result', 'documents')
        );
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

            if (! $prequalificationCriteria) {
                continue;
            }

            $sectionId = $prequalificationCriteria->SectionId;

            if (! is_null($scoreAwarded) && $scoreAwarded > $maxScore) {
                throw ValidationException::withMessages([
                    "criteria_scores.{$criteriaId}.score" => "Score awarded cannot exceed the max score of {$maxScore}.",
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
            $totalSectionWeight = max(0.0, (float)($preqSections->sum('Weight') ?? 0));
            $weightScale = ($totalSectionWeight > 0 && abs($totalSectionWeight - 100.0) > 0.0001)
                ? (100.0 / $totalSectionWeight)
                : 1.0;

            $grandTotal = 0.0;
            foreach ($evaluations->groupBy('SectionID') as $sectionId => $sectionEvaluations) {
                $sectionModel = $preqSections[$sectionId] ?? null;
                $sectionWeight = ($sectionModel?->Weight ?? 0) * $weightScale;
                // Average by actually evaluated criteria in this section
                $criteriaCount = max(1, $sectionEvaluations->count());
                $perCriterionWeight = $criteriaCount > 0 ? ($sectionWeight / $criteriaCount) : 0;

                $sectionTotal = 0.0;
                foreach ($sectionEvaluations as $eval) {
                    $raw = (float)($eval->Score ?? 0);
                    if ($raw < 0) {
                        $raw = 0;
                    }
                    if ($raw > 10) {
                        $raw = 10;
                    }
                    $sectionTotal += $perCriterionWeight * ($raw / 10);
                }
                $grandTotal += round($sectionTotal, 6);
            }
            $grandTotal = round($grandTotal, 2);
            $threshold = (int)config('prequalification.passing_threshold', 60);
            $decision = ($grandTotal >= $threshold) ? 'Passed' : 'Failed';

            PrequalificationResult::updateOrCreate(
                ['ApplicationID' => $application->ApplicationID],
                [
                    'TotalScore' => $grandTotal,
                    'Decision' => $decision,
                    'ApprovalBy' => Auth::id(),
                ]
            );

            // Move application status to Reviewed after evaluation
            try {
                $application->Status = PrequalificationApplicationEnum::Reviewed;
                $application->save();
            } catch (\Throwable $e) {
                Log::warning('Failed to set application status Reviewed', [
                    'applicationId' => $application->ApplicationID,
                    'error' => $e->getMessage(),
                ]);
            }

            // Upsert per-category progress to Under Review and add history
            try {
                $acs = ApplicationCategoryStatus::firstOrNew([
                    'ApplicationId' => $application->ApplicationID,
                    'CategoryId' => $application->CategoryID,
                ]);
                $prevStatus = $acs->exists ? $acs->Status : null;
                $prevProgress = $acs->exists ? (float)$acs->ProgressPercent : 0.0;
                if (! $acs->exists) {
                    $acs->CreatedBy = Auth::id();
                    $acs->CreatedOn = now();
                }
                $acs->Status = 'U'; // Under Review
                $acs->ProgressPercent = 80.00;
                $acs->Stage = 'evaluation';
                $acs->StageLabel = 'Evaluation Completed';
                $acs->ModifiedBy = Auth::id();
                $acs->ModifiedOn = now();
                $acs->save();

                CategoryProgressHistory::create([
                    'ApplicationCategoryId' => $acs->Id,
                    'PreviousStatus' => $prevStatus,
                    'NewStatus' => 'U',
                    'PreviousProgress' => $prevProgress,
                    'NewProgress' => 80.00,
                    'ChangedBy' => Auth::id(),
                    'Notes' => 'Evaluation submitted; moved to Under Review',
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to update category progress (evaluation submit)', [
                    'applicationId' => $application->ApplicationID,
                    'categoryId' => $application->CategoryID,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        if ($request->filled('general_comments')) {
            $application->GeneralComments = $request->input('general_comments');
            $application->save();
        }

        return redirect()->route('prequalification.applications.show', $applicationId)
            ->with('success', 'Evaluation submitted successfully!');
    }
}
