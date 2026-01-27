<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenderApiController extends Controller
{
    private const VISIBLE_STATUSES = [
        TenderStatusEnum::Published->value,
        TenderStatusEnum::OpeningInProgress->value,
    ];

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Tender::query()->with([
                'procurementMode',
                'currency',
                'tenderCategoryRelation',
                'itemCategoryRelation',
                'documents',
            ]);

            $thirdPartyId = $this->resolveThirdPartyId($request);
            $supplierIds = $this->resolveSupplierIds($thirdPartyId);

            $this->applyVisibilityScope($query, $supplierIds);
            $this->applyFilters($query, $request);

            $tenders = $query
                ->orderByDesc('CreatedOn')
                ->orderByDesc('Id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Tenders retrieved successfully.',
                'data' => $tenders,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tenders.',
            ], 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $thirdPartyId = $this->resolveThirdPartyId($request);
            $supplierIds = $this->resolveSupplierIds($thirdPartyId);

            $tender = Tender::with([
                'procurementMode',
                'currency',
                'tenderCategoryRelation',
                'itemCategoryRelation',
                'documents',
                'items.item.price',
            ])->find($id);

            if (!$tender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tender not found.',
                ], 404);
            }

            if (!$this->canAccessTender($tender, $supplierIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied.',
                ], 403);
            }

            $invitationStatus = $this->resolveInvitationStatus($tender->Id, $supplierIds);

            return response()->json([
                'success' => true,
                'message' => 'Tender retrieved successfully.',
                'data' => [
                    'id' => $tender->Id,
                    'tenderNo' => $tender->TenderNo,
                    'title' => $tender->Title,
                    'tenderType' => $tender->TenderType,
                    'status' => $tender->Status,
                    'scopeOfWork' => $tender->ScopeOfWork,
                    'instructions' => $tender->Instructions,
                    'submissionDeadline' => $tender->SubmissionDeadline,
                    'openingDate' => $tender->OpeningDate,
                    'estimatedValue' => $tender->EstimatedValue,
                    'currency' => $tender->currency,
                    'procurementMode' => $tender->procurementMode,
                    'tenderCategory' => $tender->tenderCategoryRelation,
                    'itemCategory' => $tender->itemCategoryRelation,
                    'documents' => $tender->documents,
                    'items' => $tender->items->map(fn ($item) => [
                        'id' => $item->Id,
                        'itemCode' => $item->item?->ItemCode,
                        'itemName' => $item->item?->ItemName,
                        'description' => $item->ManualItemDescription ?? $item->item?->ItemName,
                        'quantity' => $item->QtyToTender,
                        'uom' => $item->UnitOfMeasure ?? $item->Unit,
                        'estimatedUnitPrice' => $item->EstimatedUnitCost,
                        'totalEstimate' => ($item->QtyToTender ?? 0) * ($item->EstimatedUnitCost ?? 0),
                    ]),
                    'invitationStatus' => $invitationStatus,
                    'isInvited' => $invitationStatus !== null,
                    'createdOn' => $tender->CreatedOn,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tender.',
            ], 500);
        }
    }

    private function applyVisibilityScope(Builder $query, array $supplierIds): void
    {
        $query->where(function (Builder $q) use ($supplierIds) {
            $q->where(function (Builder $open) {
                $open->where('TenderType', TenderTypeEnum::Open->value)
                    ->whereIn('Status', self::VISIBLE_STATUSES);
            });

            if (!empty($supplierIds)) {
                $q->orWhere(function (Builder $restricted) use ($supplierIds) {
                    $restricted->where('TenderType', TenderTypeEnum::Restricted->value)
                        ->whereIn('Status', self::VISIBLE_STATUSES)
                        ->whereExists(function ($sub) use ($supplierIds) {
                            $sub->selectRaw(1)
                                ->from('t_TenderInvitations as ti')
                                ->whereColumn('ti.TenderId', 't_Tenders.Id')
                                ->whereIn('ti.SupplierId', $supplierIds)
                                ->whereNull('ti.DeletedOn');
                        });
                });
            }
        });
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($status = $request->query('status')) {
            $query->where('Status', $status);
        }

        if ($search = trim((string) $request->query('search'))) {
            $like = '%' . str_replace(['%', '_'], ['[%]', '[_]'], $search) . '%';
            $query->where(fn (Builder $q) =>
                $q->where('Title', 'like', $like)
                  ->orWhere('TenderNo', 'like', $like)
                  ->orWhere('ScopeOfWork', 'like', $like)
            );
        }
    }

    private function canAccessTender(Tender $tender, array $supplierIds): bool
    {
        if ($tender->TenderType === TenderTypeEnum::Open->value) {
            return in_array($tender->Status, self::VISIBLE_STATUSES, true);
        }

        if ($tender->TenderType === TenderTypeEnum::Restricted->value && !empty($supplierIds)) {
            return DB::table('t_TenderInvitations')
                ->where('TenderId', $tender->Id)
                ->whereIn('SupplierId', $supplierIds)
                ->whereNull('DeletedOn')
                ->exists();
        }

        return false;
    }

    private function resolveInvitationStatus(int $tenderId, array $supplierIds): ?string
    {
        if (empty($supplierIds)) {
            return null;
        }

        $invitation = DB::table('t_TenderInvitations')
            ->where('TenderId', $tenderId)
            ->whereIn('SupplierId', $supplierIds)
            ->whereNull('DeletedOn')
            ->first();

        return $invitation ? strtolower($invitation->ResponseStatus ?? 'pending') : null;
    }

    private function resolveThirdPartyId(Request $request): ?int
    {
        if ($id = $request->query('third_party_id')) {
            return (int) $id;
        }

        $user = Auth::guard('sanctum')->user();

        return $user?->ThirdPartyId
            ?? $user?->thirdParty?->Id
            ?? null;
    }

    private function resolveSupplierIds(?int $thirdPartyId): array
    {
        if (!$thirdPartyId) {
            return [];
        }

        return DB::table('t_Suppliers')
            ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
            ->where('t_SupplierMaster.ThirdPartyId', $thirdPartyId)
            ->whereNull('t_Suppliers.DeletedOn')
            ->pluck('t_Suppliers.Id')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }
}
