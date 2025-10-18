<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\Procurement\Prequalification\ApplicationCategoryStatus;
use App\Models\ThirdParty\SupplierCategory as SupplierCategoryModel;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationApplicationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Http\Resources\Procurement\PrequalificationRoundResource;
use App\Http\Resources\Procurement\PrequalificationApplicationResource;
use Illuminate\Support\Facades\Auth;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Models\Procurement\Prequalification\PrequalificationResult;

class PrequalificationApplicationController extends Controller
{
    public function index(): View
    {
        $applications = PrequalificationApplication::with('round', 'supplier', 'category')
            ->orderByDesc('SubmittedOn')
            ->orderByDesc('CreatedOn')
            ->paginate(10);
        return view('procurement.suppliers.prequalification.supplier-applications.index', compact('applications'));
    }

    public function show(PrequalificationApplication $application): View
    {
        // $application->load('round.masterSections.criteria');
        // return view('procurement.suppliers.prequalification.supplier-applications.show', compact('application'));

        $application->load('round.prequalificationSections.masterSection.criteria', 'category');

        return view(
            'procurement.suppliers.prequalification.supplier-applications.show',
            compact('application')
        );
    }

    public function apiIndex(Request $request): JsonResponse
    {
        try {
            // TODO: Implement proper token validation for supplier portal integration
            // Temporary bypass for supplier portal while maintaining security
            $bearerToken = $request->bearerToken();
            if (!Auth::check() && !$bearerToken) {
                return response()->json(['message' => 'Unauthorized - No authentication provided'], 401);
            }
            
            // Log authentication attempt for debugging
            if ($bearerToken) {
                Log::info('PrequalificationRounds API access with bearer token', [
                    'has_bearer_token' => !empty($bearerToken),
                    'auth_check' => Auth::check(),
                    'user_id' => Auth::id(),
                    'request_ip' => $request->ip()
                ]);
            }

            $user = Auth::user();
            Log::debug('apiIndex: authenticated user', ['id' => $user->Id ?? null, 'email' => $user->email ?? $user->Email ?? null]);
            $supplierId = $user && $user->thirdParty ? $user->thirdParty->Id : null;

            // Supplier eligibility: only supplier third parties with Approved status can apply
            $supplierEligible = false;
            if ($user && $user->thirdParty) {
                $third = $user->thirdParty->loadMissing('types');
                $typeCodes = $third->relationLoaded('types') ? $third->types->pluck('Code') : collect();
                $isSupplierUser = $typeCodes->contains(fn($c) => is_string($c) && str_starts_with($c, 'SU-'));
                $isApprovedUser = ($third->ApprovalStatus ?? null) === ThirdPartyApprovalStatusEnum::Approved;
                $supplierEligible = $isSupplierUser && $isApprovedUser;
            }

        // Get query parameters with defaults
        $page = (int) $request->get('page', 1);
        $pageSize = (int) $request->get('pageSize', 10);
        $sortBy = $request->get('sortBy', 'startDate');
        $sortOrder = $request->get('sortOrder', 'asc');
    $status = $request->get('status', 'open');
        $search = $request->get('q', '');
        
        // Validate and sanitize parameters
        $pageSize = max(1, min(100, $pageSize)); // Limit between 1-100
        $page = max(1, $page); // Minimum page 1
        
        // Validate sortBy parameter
        $allowedSortFields = ['startDate', 'endDate', 'title', 'createdOn'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'startDate';
        }
        
        // Validate sortOrder parameter
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';
        
        // Map frontend sortBy to database column names
        $sortColumnMap = [
            'startDate' => 't_PrequalificationRounds.StartDate',
            'endDate' => 't_PrequalificationRounds.EndDate', 
            'title' => 't_PrequalificationRounds.Title',
            'createdOn' => 't_PrequalificationRounds.CreatedOn'
        ];
        
        $sortColumn = $sortColumnMap[$sortBy] ?? 't_PrequalificationRounds.StartDate';

            // Build the query (rounds only; applications fetched separately)
            $query = PrequalificationRound::query()
                ->with(['sections.criteria', 'criteria.masterCriteria'])
                ->select('t_PrequalificationRounds.*');

        // Apply status filtering
        if ($status !== 'all') {
            if ($status === 'open') {
                $query->where('t_PrequalificationRounds.Status', PrequalificationRoundEnum::Open);
            } elseif ($status === 'closed') {
                $query->where('t_PrequalificationRounds.Status', PrequalificationRoundEnum::Closed);
            } elseif ($status === 'draft' || $status === 'd') {
                $query->where('t_PrequalificationRounds.Status', PrequalificationRoundEnum::Draft);
            }
        }

        // Apply search functionality
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('t_PrequalificationRounds.Title', 'LIKE', '%' . $search . '%')
                  ->orWhere('t_PrequalificationRounds.Description', 'LIKE', '%' . $search . '%');
            });
        }

        // Apply sorting
        $query->orderBy($sortColumn, $sortOrder);

            // Get total count before pagination
            $totalCount = $query->count();
            $totalPages = ceil($totalCount / $pageSize);

            // Apply pagination
            $availableRounds = $query->paginate($pageSize, ['*'], 'page', $page);

            // Build categories per round from the supplier's assigned classifications (t_ThirdParty_SupplierCategory)
            $roundIds = $availableRounds->pluck('RoundID')->filter()->values();
            $categoriesByRound = collect();
            if ($roundIds->isNotEmpty()) {
                $supplierCats = collect();
                if ($supplierId) {
                    $supplierCats = DB::table('t_ThirdParty_SupplierCategory as tpsc')
                        ->join('t_SupplierCategories as sc', 'sc.SupplierCategoryID', '=', 'tpsc.supplier_category_id')
                        ->where('tpsc.third_party_id', $supplierId)
                        ->whereNull('sc.DeletedOn')
                        ->where(function ($q) { $q->where('sc.IsActive', 1)->orWhereNull('sc.IsActive'); })
                        ->select('sc.SupplierCategoryID', 'sc.CategoryName', 'sc.Description')
                        ->orderBy('sc.CategoryName')
                        ->get()
                        ->unique('SupplierCategoryID')
                        ->values();
                }

                // Attempt to scope by round item categories if a mapping table exists
                $roundItemCategoryMap = collect();
                if (Schema::hasTable('t_PrequalificationRoundItemCategory')
                    && Schema::hasColumn('t_PrequalificationRoundItemCategory', 'RoundID')
                    && Schema::hasColumn('t_PrequalificationRoundItemCategory', 'ItemCategoryID')) {
                    $roundItemCategoryMap = DB::table('t_PrequalificationRoundItemCategory')
                        ->whereIn('RoundID', $roundIds)
                        ->whereNull('DeletedOn')
                        ->get(['RoundID', 'ItemCategoryID'])
                        ->groupBy('RoundID')
                        ->map(fn($rows) => $rows->pluck('ItemCategoryID')->filter()->unique()->values());
                } elseif (Schema::hasTable('t_PrequalificationRoundItemCategories')
                    && Schema::hasColumn('t_PrequalificationRoundItemCategories', 'RoundID')
                    && Schema::hasColumn('t_PrequalificationRoundItemCategories', 'ItemCategoryID')) {
                    $roundItemCategoryMap = DB::table('t_PrequalificationRoundItemCategories')
                        ->whereIn('RoundID', $roundIds)
                        ->whereNull('DeletedOn')
                        ->get(['RoundID', 'ItemCategoryID'])
                        ->groupBy('RoundID')
                        ->map(fn($rows) => $rows->pluck('ItemCategoryID')->filter()->unique()->values());
                }

                $categoriesByRound = $roundIds->mapWithKeys(function ($rid) use ($supplierCats, $roundItemCategoryMap) {
                    // Helper to fetch all active categories when we have nothing supplier-specific
                    $getAllActiveCats = function () {
                        return DB::table('t_SupplierCategories as sc')
                            ->whereNull('sc.DeletedOn')
                            ->where(function ($q) { $q->where('sc.IsActive', 1)->orWhereNull('sc.IsActive'); })
                            ->select('sc.SupplierCategoryID', 'sc.CategoryName', 'sc.Description')
                            ->orderBy('sc.CategoryName')
                            ->get();
                    };

                    // If no mapping exists at all, use supplier categories if present; otherwise fall back to all active
                    if ($roundItemCategoryMap->isEmpty()) {
                        return [$rid => $supplierCats->isNotEmpty() ? $supplierCats : $getAllActiveCats()];
                    }
                    $itemIds = $roundItemCategoryMap->get($rid, collect());
                    if (!$itemIds instanceof \Illuminate\Support\Collection) {
                        $itemIds = collect($itemIds);
                    }
                    if ($itemIds->isEmpty()) {
                        // No items mapped for this round — use supplier categories if available; else all active categories
                        return [$rid => $supplierCats->isNotEmpty() ? $supplierCats : $getAllActiveCats()];
                    }
                    // Find supplier categories that map to any of the round item categories
                    $allowedCatIds = DB::table('t_SupplierCategory_ItemCategory as scic')
                        ->whereIn('scic.ItemCategoryID', $itemIds->all())
                        ->whereNull('scic.DeletedOn')
                        ->pluck('scic.SupplierCategoryID')
                        ->unique()
                        ->values();

                    // If no intersection, gracefully fall back to supplier categories or all active categories
                    if ($allowedCatIds->isEmpty()) {
                        if ($supplierCats->isNotEmpty()) {
                            return [$rid => $supplierCats];
                        }
                        return [$rid => $getAllActiveCats()];
                    }

                    $filtered = $supplierCats->filter(function ($row) use ($allowedCatIds) {
                        return $allowedCatIds->contains($row->SupplierCategoryID);
                    })->values();

                    return [$rid => $filtered];
                });
            }

            // Fetch applications for current supplier across these rounds
            $applications = collect();
            if ($supplierId && $roundIds->isNotEmpty()) {
                $applications = PrequalificationApplication::query()
                    ->where('SupplierID', $supplierId)
                    ->whereIn('RoundID', $roundIds)
                    ->whereNull('DeletedOn')
                    ->select('ApplicationID', 'RoundID', 'CategoryID', 'SubmittedOn', 'Status')
                    ->get();
            }

            // Map applications by RoundID:CategoryID
            $appsByKey = $applications->keyBy(function ($a) {
                return $a->RoundID . ':' . $a->CategoryID;
            });

            // Fetch per-category status for these applications
            $appIds = $applications->pluck('ApplicationID');
            $catStatuses = collect();
            if ($appIds->isNotEmpty()) {
                $catStatuses = ApplicationCategoryStatus::query()
                    ->whereIn('ApplicationId', $appIds)
                    ->get()
                    ->groupBy('ApplicationId');
            }

            // Fetch persisted results per application to expose actual awarded % per category
            $resultsByAppId = collect();
            if ($appIds->isNotEmpty()) {
                $resultsByAppId = PrequalificationResult::query()
                    ->whereIn('ApplicationID', $appIds)
                    ->get()
                    ->keyBy('ApplicationID');
            }

            // Helper to map status codes/enums to required labels
            $mapStatus = function ($appStatusCode = null, $catStatusCode = null, $stage = null) {
                // Category status overrides application status when present
                $code = $catStatusCode ?? $appStatusCode;
                if (!$code) return 'NOT_APPLIED';
                $code = is_string($code) ? $code : (string) $code;
                return match ($code) {
                    'A', 'P' => 'APPROVED',
                    'R' => 'REJECTED',
                    'V' => 'UNDER_REVIEW', // Reviewed/Verification -> treated as under review for UI
                    'S' => 'SUBMITTED',
                    default => $stage && str_contains(strtolower($stage), 'review') ? 'UNDER_REVIEW' : 'SUBMITTED',
                };
            };

            // Build output rounds with categories array
            $data = $availableRounds->map(function ($round) use ($categoriesByRound, $appsByKey, $catStatuses, $mapStatus, $applications, $supplierId, $supplierEligible, $resultsByAppId) {
                $roundId = $round->RoundID;
                $roundCats = $categoriesByRound->get($roundId, collect());
                $cats = $roundCats->map(function ($cat) use ($roundId, $appsByKey, $catStatuses, $mapStatus, $resultsByAppId) {
                    $key = $roundId . ':' . $cat->SupplierCategoryID;
                    /** @var \App\Models\Procurement\Prequalification\PrequalificationApplication|null $app */
                    $app = $appsByKey->get($key);
                    $statusRow = null;
                    if ($app) {
                        $statusRow = optional($catStatuses->get($app->ApplicationID))->firstWhere('CategoryId', $cat->SupplierCategoryID);
                    }

                    $hasApplied = (bool) $app;
                    $resultRow = $app ? $resultsByAppId->get($app->ApplicationID) : null;
                    $progress = $resultRow?->TotalScore ?? ($statusRow?->ProgressPercent ?? 0);
                    $stage = $statusRow?->Stage ?? null;
                    $stageLabel = $statusRow?->StageLabel ?? null;
                    $decisionDate = $statusRow && $statusRow->DecisionDate ? $statusRow->DecisionDate->format('Y-m-d') : null;
                    $updatedOn = $statusRow && $statusRow->ModifiedOn ? $statusRow->ModifiedOn->format('Y-m-d') : ($statusRow && $statusRow->CreatedOn ? $statusRow->CreatedOn->format('Y-m-d') : null);
                    $status = $mapStatus($app?->Status?->value ?? (is_string($app?->Status) ? $app->Status : null), $statusRow?->Status ?? null, $stage);

                    $out = [
                        'id' => (int) $cat->SupplierCategoryID,
                        'name' => $cat->CategoryName,
                        'description' => $cat->Description,
                        'has_applied' => $hasApplied,
                        'hasApplied' => $hasApplied, // alias for frontend normalization
                        'status' => $hasApplied ? $status : 'NOT_APPLIED',
                        'progress_percent' => (float) $progress,
                    ];

                    if ($hasApplied) {
                        $out['application_id'] = (string) $app->ApplicationID;
                        $out['applicationId'] = (string) $app->ApplicationID; // alias
                        $out['application_date'] = $app->SubmittedOn ? $app->SubmittedOn->format('Y-m-d') : null;
                        $out['applicationDate'] = $out['application_date']; // alias
                        $out['stage'] = $stage;
                        $out['stage_label'] = $stageLabel;
                        $out['updated_on'] = $updatedOn;
                        $out['updatedOn'] = $updatedOn; // alias
                        $out['decision_date'] = $decisionDate;
                        $out['decisionDate'] = $decisionDate; // alias
                        if ($status === 'REJECTED') {
                            $out['rejection_reason'] = $statusRow->RejectionReason ?? null;
                        }
                    }

                    return $out;
                })->values();

                // Compute round-level eligibility helpers (day-level; today is applicable)
                $now = now()->startOfDay();
                $windowOpen = (!$round->StartDate || $round->StartDate <= $now) && (!$round->EndDate || $round->EndDate >= $now);
                $statusValue = is_object($round->Status) && property_exists($round->Status, 'value') ? $round->Status->value : (string) $round->Status;
                $statusOpen = strtolower((string) $statusValue) === 'open' || (defined('App\\Enums\\Procurement\\PrequalificationRoundEnum::Open') && (string) $statusValue === (string) \App\Enums\Procurement\PrequalificationRoundEnum::Open->value);
                $isClosed = (string) $statusValue === (string) \App\Enums\Procurement\PrequalificationRoundEnum::Closed->value;
                $isExpired = $round->EndDate && $round->EndDate < $now;
                $hasCategories = $cats->count() > 0;
                $roundAppsCount = $applications->where('RoundID', $roundId)->count();
                $hasUnapplied = $cats->contains(function ($c) { return empty($c['has_applied']); });
                $supplierHasNoAppsInRound = $roundAppsCount === 0;
                // Enforce Closed and Expired
                $backendCanApply = $supplierId !== null && $windowOpen && $statusOpen && $hasCategories && !$isClosed && !$isExpired;

                // New flags
                $isFutureWindow = ($round->StartDate && $round->StartDate > $now);

                // Mark as duplicate/not applicable when another round covers this window and was created earlier
                $primaryCovering = \App\Models\Procurement\Prequalification\PrequalificationRound::query()
                    ->where('StartDate', '=', $round->StartDate)
                    ->where('EndDate', '=', $round->EndDate)
                    ->where('Status', \App\Enums\Procurement\PrequalificationRoundEnum::Open)
                    ->where(\App\Models\Procurement\Prequalification\PrequalificationRound::getPrimaryKey(), '!=', $roundId)
                    ->orderBy('CreatedOn', 'asc')
                    ->first(['RoundID', 'Title', 'CreatedOn', 'StartDate', 'EndDate']);

                $duplicateWithinRange = false;
                $primaryWindowRoundId = null;
                $primaryWindowRoundTitle = null;
                if ($primaryCovering) {
                    // Only latest shows Not Applicable: mark duplicate if primary (earlier) exists
                    $duplicateWithinRange = $primaryCovering->CreatedOn && $round->CreatedOn
                        ? ($primaryCovering->CreatedOn < $round->CreatedOn)
                        : true; // fallback to true if timestamps unavailable
                    if ($duplicateWithinRange) {
                        $primaryWindowRoundId = (int) $primaryCovering->RoundID;
                        $primaryWindowRoundTitle = (string) $primaryCovering->Title;
                    }
                }

                // Final canApply must also respect supplier eligibility and not-applicable conditions
                $canApply = $backendCanApply
                    && ($hasUnapplied || $supplierHasNoAppsInRound)
                    && $supplierEligible
                    && !$isFutureWindow
                    && !$duplicateWithinRange;

                // Not Applicable per business rules only:
                // - expired
                // - strictly future (today is applicable)
                // - no classifications
                // - already applied to all classifications
                $notApplicable = (bool) ($isExpired || $isFutureWindow || !$hasCategories || !$hasUnapplied);

                return [
                    'id' => (int) $round->RoundID,
                    'title' => $round->Title,
                    'status' => is_object($round->Status) && property_exists($round->Status, 'value') ? $round->Status->value : (string) $round->Status,
                    'startDate' => $round->StartDate ? $round->StartDate->format('Y-m-d') : null,
                    'endDate' => $round->EndDate ? $round->EndDate->format('Y-m-d') : null,
                    'maxVendors' => $round->MaxVendors,
                    'categories' => $cats,
                    'canApply' => (bool) $canApply,
                    'isClosed' => (bool) $isClosed,
                    'isExpired' => (bool) $isExpired,
                    'windowOpen' => (bool) $windowOpen,
                    'canApplyToMore' => (bool) $hasUnapplied,
                    'categoryCount' => $cats->count(),
                    'appliedCount' => $cats->where('has_applied', true)->count(),
                    'unappliedCount' => $cats->where('has_applied', false)->count(),
                    // New flags for frontend alignment
                    'supplierEligible' => (bool) $supplierEligible,
                    'isFutureWindow' => (bool) $isFutureWindow,
                    'duplicateWithinRange' => (bool) $duplicateWithinRange,
                    'primaryWindowRoundId' => $primaryWindowRoundId,
                    'primaryWindowRoundTitle' => $primaryWindowRoundTitle,
                    // Applicability helpers for frontend
                    'hasClassifications' => (bool) $hasCategories,
                    'hasRemainingClassifications' => (bool) $hasUnapplied,
                    'notApplicable' => (bool) $notApplicable,
                ];
            })->values();

            return response()->json([
                'data' => $data,
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $totalCount,
                'totalPages' => $totalPages,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'filters' => [
                    'status' => $status,
                    'q' => $search
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Prequalification apiIndex failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to fetch rounds. Please try again later.',
            ], 500);
        }
    }

    public function apiShow(PrequalificationRound $round): JsonResponse
    {
        try {
            $round->load(['sections.criteria.masterCriteria', 'applications']);
            return (new PrequalificationRoundResource($round))->response();
        } catch (\Throwable $e) {
            Log::error('Prequalification apiShow failed', [
                'round' => $round->RoundID ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to fetch round. Please try again later.',
            ], 500);
        }
    }

    public function store(StorePrequalificationApplicationRequest $request): JsonResponse
    {
        if (!Auth::check()) return response()->json(['error' => 'User not authenticated'], 401);

        $user = Auth::user();
        if (!$user->thirdParty) return response()->json(['error' => 'User not associated with a third party.'], 400);

        $validatedData = $request->validated();
        $supplierId = $user->thirdParty->Id;
        $roundId = $validatedData['round_id'];
        $categoryIds = $validatedData['category_ids'] ?? [];

        // normalize unique ids
        $categoryIds = array_values(array_unique(array_filter($categoryIds, 'is_numeric')));

        if (empty($categoryIds)) {
            return response()->json(['message' => 'At least one category must be selected.'], 422);
        }

        // check duplicates: existing rows for same supplier+round with any of these category IDs (ignore soft-deleted)
        $existing = PrequalificationApplication::query()
            ->where('SupplierID', $supplierId)
            ->where('RoundID', $roundId)
            ->whereNull('DeletedOn')
            ->whereIn('CategoryID', $categoryIds)
            ->pluck('CategoryID')
            ->toArray();

        if (!empty($existing)) {
            // fetch category names for better message
            $dupNames = \App\Models\ThirdParty\SupplierCategory::whereIn('SupplierCategoryID', $existing)
                ->pluck('CategoryName', 'SupplierCategoryID')
                ->toArray();

            $duplicates = [];
            foreach ($existing as $cid) {
                $duplicates[] = ['id' => $cid, 'name' => $dupNames[$cid] ?? null];
            }

            return response()->json([
                'message' => 'Some categories have already been applied for this round.',
                'duplicates' => $duplicates,
            ], 409);
        }

        // server-side guard: round must be Open, within window, not expired, not closed
        $round = PrequalificationRound::query()->find($roundId);
        if (!$round) return response()->json(['error' => 'Round not found.'], 404);
        $now = now();
        $statusValue = is_object($round->Status) && property_exists($round->Status, 'value') ? $round->Status->value : (string) $round->Status;
    $isClosed = (string) $statusValue === (string) \App\Enums\Procurement\PrequalificationRoundEnum::Closed->value;
    $isOpen = (string) $statusValue === (string) \App\Enums\Procurement\PrequalificationRoundEnum::Open->value;
        $windowOpen = (!$round->StartDate || $round->StartDate <= $now) && (!$round->EndDate || $round->EndDate >= $now);
        $isExpired = $round->EndDate && $round->EndDate < $now;
        if ($isClosed || !$isOpen || !$windowOpen || $isExpired) {
            $reason = $isExpired ? 'This round has expired.' : ($isClosed ? 'Applications are closed for this round.' : (!$isOpen ? 'Round is not open for applications.' : 'Application window is not active.'));
            // 410 Gone for expired, 403 Forbidden for closed/not-open
            $code = $isExpired ? 410 : 403;
            return response()->json(['message' => $reason], $code);
        }

        // create records — one row per category
        DB::beginTransaction();
        try {
            $createdIds = [];
            foreach ($categoryIds as $cid) {
                $app = PrequalificationApplication::create([
                    'RoundID' => $roundId,
                    'SupplierID' => $supplierId,
                    'CategoryID' => $cid,
                    'Status' => PrequalificationApplicationEnum::Submitted,
                    'SubmittedOn' => now(),
                    'CreatedBy' => $user->Id,
                ]);
                $createdIds[] = $app->ApplicationID;
            }
            DB::commit();

            return response()->json([
                'message' => 'Application submitted.',
                'applicationIds' => $createdIds,
                'roundId' => $roundId,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create prequalification application', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to submit application.'], 500);
        }
    }

    public function destroy(PrequalificationApplication $application)
    {
        try {
            $application->delete();
            return redirect()
                ->route('prequalification.applications.index')
                ->with('success', 'Application deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete application: ' . $e->getMessage());
            return redirect()
                ->route('prequalification.applications.index')
                ->with('error', 'Failed to delete application. Please try again.');
        }
    }
}
