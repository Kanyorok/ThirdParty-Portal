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
use App\Traits\Model\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class PreqApplicationController extends Controller
{
    use ApiResponseTrait;

    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Unauthenticated', 401);
            }

            $thirdPartyId = $user->third_party_id ?? ($user->thirdParty?->Id);

            $supplierMaster = $thirdPartyId
                ? SupplierMaster::where('ThirdPartyId', $thirdPartyId)->first()
                : null;

            $supplierId = $supplierMaster?->Id;
            $supplierEligible = $supplierMaster
                ? ($supplierMaster->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved)
                : false;

            $page = max(1, (int) $request->integer('page', 1));
            $pageSize = max(1, min(100, (int) $request->integer('pageSize', 10)));
            $statusFilter = (string) $request->get('status', 'open');
            $search = trim((string) $request->get('q', ''));

            $query = PrequalificationRound::query()
                ->select('t_PrequalificationRounds.*')
                ->with([
                    'supplierCategories' => fn ($q) => $q->wherePivot('ThirdPartyID', $thirdPartyId),
                ]);

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

            $roundIds = $rounds->getCollection()->pluck('RoundID')->filter()->values();

            $applications = $supplierId
                ? PrequalificationApplication::where('SupplierID', $supplierId)
                    ->whereIn('RoundID', $roundIds)
                    ->get()
                : collect();

            $appsByKey = $applications->keyBy(fn ($a) => "{$a->RoundID}:{$a->CategoryID}");

            $today = now()->startOfDay();

            $data = $rounds->getCollection()->map(function ($round) use ($appsByKey, $supplierEligible, $today) {
                $isExpired = $round->EndDate && $round->EndDate < $today;

                $categories = $round->supplierCategories
                ->filter(fn ($cat) => (int) ($cat->pivot?->ThirdPartyID ?? 0) === (int) $thirdPartyId)
                ->map(function ($cat) use ($round, $appsByKey) {
                    $app = $appsByKey->get("{$round->RoundID}:{$cat->SupplierCategoryID}");

                    return [
                        'id' => (int) $cat->SupplierCategoryID,
                        'name' => $cat->CategoryName,
                        'code' => $cat->Code ?? null,
                        'has_applied' => (bool) $app,
                        'application_id' => $app ? (string) $app->ApplicationID : null,
                        'status' => $app ? ($app->Status?->value ?? 'SUBMITTED') : 'NOT_APPLIED',
                    ];
                })
                ->values();

                return [
                    'id' => (int) $round->RoundID,
                    'title' => $round->Title,
                    'start_date' => $round->StartDate?->format('Y-m-d'),
                    'end_date' => $round->EndDate?->format('Y-m-d'),
                    'categories' => $categories,
                    'supplier_eligible' => $supplierEligible,
                    'can_apply' => $supplierEligible
                        && !$isExpired
                        && $round->Status === PrequalificationRoundEnum::Open,
                ];
            })->values();

            return $this->successResponse([
                'items' => $data,
                'total' => $rounds->total(),
                'page' => $rounds->currentPage(),
                'page_size' => $rounds->perPage(),
            ], 'Prequalification rounds loaded.', 200);
        } catch (Throwable $e) {
            return $this->errorResponse(
                'Internal Server Error',
                500,
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    public function store(StorePrequalificationApplicationRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $supplierMaster = SupplierMaster::where('ThirdPartyId', $user->third_party_id)->first();

        if (!$supplierMaster) {
            return $this->errorResponse('No supplier profile found', 400);
        }

        $validated = $request->validated();
        $roundId = (int) $validated['round_id'];
        $categoryIds = array_values(array_unique(array_map('intval', $validated['category_ids'])));

        DB::beginTransaction();

        try {
            $existing = PrequalificationApplication::where('SupplierID', $supplierMaster->Id)
                ->where('RoundID', $roundId)
                ->whereIn('CategoryID', $categoryIds)
                ->pluck('CategoryID')
                ->all();

            $existingSet = array_flip(array_map('intval', $existing));
            $toCreate = array_values(array_filter($categoryIds, fn ($cid) => !isset($existingSet[$cid])));

            $ids = [];

            foreach ($toCreate as $cid) {
                $app = PrequalificationApplication::create([
                    'RoundID' => $roundId,
                    'SupplierID' => $supplierMaster->Id,
                    'CategoryID' => $cid,
                    'Status' => PrequalificationApplicationEnum::Submitted,
                    'SubmittedOn' => now(),
                    'CreatedBy' => $user->Id,
                ]);

                $ids[] = (string) $app->ApplicationID;
            }

            DB::commit();

            return $this->successResponse([
                'application_ids' => $ids,
                'created' => count($ids),
                'skipped' => count($existing),
            ], 'Application submitted successfully.', 201);
        } catch (Throwable $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Submission failed',
                500,
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }
}
