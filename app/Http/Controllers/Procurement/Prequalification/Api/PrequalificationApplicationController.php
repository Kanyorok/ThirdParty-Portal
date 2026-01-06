<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationApplicationRequest;
use App\Http\Resources\Procurement\PrequalificationRoundResource;
use App\Models\Procurement\Prequalification\ApplicationCategoryStatus;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationApplicationDocument;
use App\Models\Procurement\Prequalification\PrequalificationResult;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\ThirdParty\SupplierMaster;
use App\Models\ThirdParty\SupplierCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PrequalificationApplicationController extends Controller
{
    public function index(): View
    {
        $applications = PrequalificationApplication::with('round', 'supplier.party', 'category')
            ->orderByDesc('SubmittedOn')
            ->orderByDesc('CreatedOn')
            ->paginate(10);

        return view('procurement.suppliers.prequalification.supplier-applications.index', compact('applications'));
    }

    public function show(PrequalificationApplication $application): View
    {
        $application->load('round.prequalificationSections.masterSection.criteria', 'category', 'supplier.party');

        return view('procurement.suppliers.prequalification.supplier-applications.show', compact('application'));
    }

    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $supplierId = null;
            $supplierMaster = null;
            $supplierEligible = false;

            if ($user->thirdParty) {
                $supplierMaster = SupplierMaster::where('ThirdPartyId', $user->thirdParty->Id)->first();
                if ($supplierMaster) {
                    $supplierId = $supplierMaster->Id;
                    $supplierEligible = ($supplierMaster->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved);
                }
            }

            $page = max(1, (int) $request->get('page', 1));
            $pageSize = max(1, min(100, (int) $request->get('pageSize', 10)));
            $sortBy = $request->get('sortBy', 'startDate');
            $sortOrder = in_array(strtolower($request->get('sortOrder', 'desc')), ['asc', 'desc']) ? strtolower($request->get('sortOrder')) : 'desc';
            $status = $request->get('status', 'open');
            $search = $request->get('q', '');

            $sortColumnMap = [
                'startDate' => 't_PrequalificationRounds.StartDate',
                'endDate'   => 't_PrequalificationRounds.EndDate',
                'title'     => 't_PrequalificationRounds.Title',
                'createdOn' => 't_PrequalificationRounds.CreatedOn'
            ];
            $sortColumn = $sortColumnMap[$sortBy] ?? 't_PrequalificationRounds.StartDate';

            $query = PrequalificationRound::query()
                ->with(['sections.masterSection', 'sections.criteria', 'criteria.masterCriteria'])
                ->select('t_PrequalificationRounds.*');

            if ($status !== 'all') {
                $enumValue = match ($status) {
                    'open' => PrequalificationRoundEnum::Open,
                    'closed' => PrequalificationRoundEnum::Closed,
                    'draft' => PrequalificationRoundEnum::Draft,
                    default => null
                };
                if ($enumValue) $query->where('t_PrequalificationRounds.Status', $enumValue);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('t_PrequalificationRounds.Title', 'LIKE', "%{$search}%")
                        ->orWhere('t_PrequalificationRounds.Description', 'LIKE', "%{$search}%");
                });
            }

            $availableRounds = $query->orderBy($sortColumn, $sortOrder)->paginate($pageSize, ['*'], 'page', $page);
            $roundIds = $availableRounds->pluck('RoundID')->filter()->values();

            $categoriesByRound = collect();
            if ($roundIds->isNotEmpty()) {
                $supplierCats = collect();
                if ($supplierId) {
                    $supplierCats = DB::table('t_ThirdParty_SupplierCategory as tpsc')
                        ->join('t_SupplierCategories as sc', 'sc.SupplierCategoryID', '=', 'tpsc.supplier_category_id')
                        ->where('tpsc.third_party_id', $user->thirdParty->Id)
                        ->whereNull('sc.DeletedOn')
                        ->where(fn($q) => $q->where('sc.IsActive', 1)->orWhereNull('sc.IsActive'))
                        ->select('sc.SupplierCategoryID', 'sc.CategoryName', 'sc.Description')
                        ->get()->unique('SupplierCategoryID')->values();
                }

                $roundItemTable = Schema::hasTable('t_PrequalificationRoundItemCategory') ? 't_PrequalificationRoundItemCategory' : (Schema::hasTable('t_PrequalificationRoundItemCategories') ? 't_PrequalificationRoundItemCategories' : null);
                $roundItemCategoryMap = $roundItemTable ? DB::table($roundItemTable)->whereIn('RoundID', $roundIds)->whereNull('DeletedOn')->get()->groupBy('RoundID')->map(fn($rows) => $rows->pluck('ItemCategoryID')->unique()) : collect();

                $categoriesByRound = $roundIds->mapWithKeys(function ($rid) use ($supplierCats, $roundItemCategoryMap) {
                    $itemIds = $roundItemCategoryMap->get($rid, collect());
                    if ($itemIds->isEmpty()) {
                        return [$rid => $supplierCats->isNotEmpty() ? $supplierCats : DB::table('t_SupplierCategories')->whereNull('DeletedOn')->where(fn($q) => $q->where('IsActive', 1)->orWhereNull('IsActive'))->get()];
                    }
                    $allowedCatIds = DB::table('t_SupplierCategory_ItemCategory')->whereIn('ItemCategoryID', $itemIds)->whereNull('DeletedOn')->pluck('SupplierCategoryID')->unique();
                    return [$rid => $supplierCats->filter(fn($row) => $allowedCatIds->contains($row->SupplierCategoryID))->values()];
                });
            }

            $applications = ($supplierId && $roundIds->isNotEmpty()) ? PrequalificationApplication::where('SupplierID', $supplierId)->whereIn('RoundID', $roundIds)->whereNull('DeletedOn')->get() : collect();
            $appsByKey = $applications->keyBy(fn($a) => "{$a->RoundID}:{$a->CategoryID}");
            $appIds = $applications->pluck('ApplicationID');
            $catStatuses = $appIds->isNotEmpty() ? ApplicationCategoryStatus::whereIn('ApplicationId', $appIds)->get()->groupBy('ApplicationId') : collect();
            $resultsByAppId = $appIds->isNotEmpty() ? PrequalificationResult::whereIn('ApplicationID', $appIds)->get()->keyBy('ApplicationID') : collect();

            $data = $availableRounds->map(function ($round) use ($categoriesByRound, $appsByKey, $catStatuses, $supplierId, $supplierEligible, $resultsByAppId) {
                $roundCats = $categoriesByRound->get($round->RoundID, collect());
                $cats = $roundCats->map(function ($cat) use ($round, $appsByKey, $catStatuses, $resultsByAppId) {
                    $app = $appsByKey->get("{$round->RoundID}:{$cat->SupplierCategoryID}");
                    $statusRow = $app ? optional($catStatuses->get($app->ApplicationID))->firstWhere('CategoryId', $cat->SupplierCategoryID) : null;
                    $hasApplied = (bool)$app;

                    return [
                        'id' => (int) $cat->SupplierCategoryID,
                        'name' => $cat->CategoryName,
                        'hasApplied' => $hasApplied,
                        'status' => $hasApplied ? ($statusRow->Status ?? $app->Status->value ?? 'SUBMITTED') : 'NOT_APPLIED',
                        'progress_percent' => (float) ($resultsByAppId->get($app?->ApplicationID)?->TotalScore ?? $statusRow?->ProgressPercent ?? 0),
                        'applicationId' => $app ? (string)$app->ApplicationID : null,
                    ];
                });

                $now = now()->startOfDay();
                $isExpired = $round->EndDate && $round->EndDate < $now;
                $hasUnapplied = $cats->contains('hasApplied', false);

                return [
                    'id' => (int) $round->RoundID,
                    'title' => $round->Title,
                    'status' => $round->Status->value ?? (string)$round->Status,
                    'startDate' => $round->StartDate?->format('Y-m-d'),
                    'endDate' => $round->EndDate?->format('Y-m-d'),
                    'categories' => $cats,
                    'canApply' => $supplierEligible && !$isExpired && $hasUnapplied && $round->Status === PrequalificationRoundEnum::Open,
                    'notApplicable' => $isExpired || $cats->isEmpty() || !$hasUnapplied
                ];
            });

            return response()->json([
                'data' => $data,
                'total' => $availableRounds->total(),
                'page' => $availableRounds->currentPage(),
                'pageSize' => $availableRounds->perPage(),
                'totalPages' => $availableRounds->lastPage()
            ]);
        } catch (\Throwable $e) {
            Log::error('apiIndex failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    public function store(StorePrequalificationApplicationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $supplierMaster = SupplierMaster::where('ThirdPartyId', $user->thirdParty->Id)->first();
        if (!$supplierMaster) return response()->json(['error' => 'Supplier profile not found'], 400);

        $validated = $request->validated();
        $categoryIds = array_values(array_unique(array_filter($validated['category_ids'], 'is_numeric')));

        $existing = PrequalificationApplication::where('SupplierID', $supplierMaster->Id)
            ->where('RoundID', $validated['round_id'])
            ->whereIn('CategoryID', $categoryIds)
            ->whereNull('DeletedOn')
            ->exists();

        if ($existing) return response()->json(['message' => 'Duplicate application detected'], 409);

        DB::beginTransaction();
        try {
            $createdIds = [];
            foreach ($categoryIds as $cid) {
                $app = PrequalificationApplication::create([
                    'RoundID' => $validated['round_id'],
                    'SupplierID' => $supplierMaster->Id,
                    'CategoryID' => $cid,
                    'Status' => PrequalificationApplicationEnum::Submitted,
                    'SubmittedOn' => now(),
                    'CreatedBy' => $user->Id,
                ]);
                $createdIds[] = $app->ApplicationID;

                PrequalificationApplicationDocument::where('SupplierID', $supplierMaster->Id)
                    ->where('RoundID', $validated['round_id'])
                    ->where('CategoryID', $cid)
                    ->whereNull('ApplicationID')
                    ->update(['ApplicationID' => $app->ApplicationID, 'ModifiedBy' => $user->Id, 'ModifiedOn' => now()]);
            }
            DB::commit();
            return response()->json(['applicationIds' => $createdIds], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Store application failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Submission failed'], 500);
        }
    }

    public function destroy(PrequalificationApplication $application)
    {
        try {
            $application->delete();
            return redirect()->route('prequalification.applications.index')->with('success', 'Deleted');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Delete failed');
        }
    }
}
