<?php

namespace App\Http\Controllers\Procurement\Prequalification\Api;

use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationApplicationRequest;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\ThirdParty\SupplierMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PrequalificationApplicationController extends Controller
{
    public function index(): View
    {
        $applications = PrequalificationApplication::with('round', 'supplier.party', 'category')
            ->orderByDesc('SubmittedOn')
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
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $supplierId = null;
            $supplierEligible = false;
            $thirdPartyId = $user->third_party_id ?? ($user->thirdParty->id ?? null);

            if ($thirdPartyId) {
                $supplierMaster = SupplierMaster::where('ThirdPartyId', $thirdPartyId)->first();
                if ($supplierMaster) {
                    $supplierId = $supplierMaster->Id;
                    $supplierEligible = $supplierMaster->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved;
                }
            }

            $page = max(1, (int) $request->get('page', 1));
            $pageSize = max(1, min(100, (int) $request->get('pageSize', 10)));
            $statusFilter = $request->get('status', 'open');
            $search = $request->get('q', '');

            $query = PrequalificationRound::query()
                ->with(['sections.masterSection', 'sections.criteria'])
                ->select('t_PrequalificationRounds.*');

            if ($statusFilter !== 'all') {
                $query->where('Status', match ($statusFilter) {
                    'closed' => PrequalificationRoundEnum::Closed,
                    'draft' => PrequalificationRoundEnum::Draft,
                    default => PrequalificationRoundEnum::Open,
                });
            }

            if ($search !== '') {
                $query->where('Title', 'LIKE', "%{$search}%");
            }

            $rounds = $query->latest('CreatedOn')->paginate($pageSize, ['*'], 'page', $page);
            $roundIds = $rounds->pluck('RoundID')->filter()->values();

            $categoriesByRound = collect();
            if ($roundIds->isNotEmpty() && $thirdPartyId) {
                $supplierCats = DB::table('t_ThirdParty_SupplierCategory as tpsc')
                    ->join('t_SupplierCategories as sc', 'sc.SupplierCategoryID', '=', 'tpsc.supplier_category_id')
                    ->where('tpsc.third_party_id', $thirdPartyId)
                    ->whereNull('sc.DeletedOn')
                    ->select('sc.SupplierCategoryID', 'sc.CategoryName')
                    ->get();

                $categoriesByRound = $roundIds->mapWithKeys(fn ($rid) => [$rid => $supplierCats]);
            }

            $applications = $supplierId
                ? PrequalificationApplication::where('SupplierID', $supplierId)->whereIn('RoundID', $roundIds)->get()
                : collect();

            $appsByKey = $applications->keyBy(fn ($a) => "{$a->RoundID}:{$a->CategoryID}");

            $data = $rounds->map(function ($round) use ($categoriesByRound, $appsByKey, $supplierEligible) {
                $roundCats = $categoriesByRound->get($round->RoundID, collect());

                $categories = $roundCats->map(function ($cat) use ($round, $appsByKey) {
                    $app = $appsByKey->get("{$round->RoundID}:{$cat->SupplierCategoryID}");
                    return [
                        'id' => (int) $cat->SupplierCategoryID,
                        'name' => $cat->CategoryName,
                        'hasApplied' => (bool) $app,
                        'applicationId' => $app ? (string) $app->ApplicationID : null,
                        'status' => $app ? ($app->Status->value ?? 'SUBMITTED') : 'NOT_APPLIED',
                    ];
                });

                $now = now()->startOfDay();
                $isExpired = $round->EndDate && $round->EndDate < $now;

                return [
                    'id' => (int) $round->RoundID,
                    'title' => $round->Title,
                    'startDate' => $round->StartDate?->format('Y-m-d'),
                    'endDate' => $round->EndDate?->format('Y-m-d'),
                    'categories' => $categories,
                    'canApply' => $supplierEligible && !$isExpired && $round->Status === PrequalificationRoundEnum::Open,
                    'supplierEligible' => $supplierEligible,
                ];
            });

            return response()->json([
                'data' => $data,
                'total' => $rounds->total(),
                'page' => $rounds->currentPage(),
                'pageSize' => $rounds->perPage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Prequalification API Error', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    public function store(StorePrequalificationApplicationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $thirdPartyId = $user->third_party_id ?? null;

        if (!$thirdPartyId) {
            return response()->json(['error' => 'No organization profile found'], 400);
        }

        $supplierMaster = SupplierMaster::where('ThirdPartyId', $thirdPartyId)->first();

        if (!$supplierMaster) {
            return response()->json(['error' => 'Supplier onboarding incomplete'], 400);
        }

        if ($supplierMaster->ApprovalStatus !== ThirdPartyApprovalStatusEnum::Approved) {
            return response()->json(['error' => 'Supplier profile not approved'], 403);
        }

        $validated = $request->validated();
        $categoryIds = array_unique($validated['category_ids']);

        DB::beginTransaction();
        try {
            $ids = [];
            foreach ($categoryIds as $cid) {
                $app = PrequalificationApplication::create([
                    'RoundID' => $validated['round_id'],
                    'SupplierID' => $supplierMaster->Id,
                    'CategoryID' => $cid,
                    'Status' => PrequalificationApplicationEnum::Submitted,
                    'SubmittedOn' => now(),
                    'CreatedBy' => $user->user_id ?? $user->id,
                ]);
                $ids[] = $app->ApplicationID;
            }
            DB::commit();
            return response()->json(['ids' => $ids], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Submission failed'], 500);
        }
    }
}
