<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Tenders\AddTenderItemRequest;
use App\Http\Requests\Procurement\Tenders\AddTenderSupplierRequest;
use App\Http\Requests\Procurement\Tenders\StoreTenderRequest;
use App\Http\Requests\Procurement\Tenders\TenderIndexRequest;
use App\Http\Requests\Procurement\Tenders\UpdateTenderRequest;
use App\Http\Resources\Procurement\TenderItemResource;
use App\Http\Resources\Procurement\TenderResource;
use App\Http\Resources\Procurement\TenderSupplierResource;
use App\Models\Procurement\ModeTimeline;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderItems;
use App\Models\Procurement\TenderStage;
use App\Models\Procurement\TenderSupplier;
use App\Services\Procurement\Tendering\TenderService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TenderApiController extends Controller
{
    private const VISIBLE_STATUSES = [
        TenderStatusEnum::Published->value,
        TenderStatusEnum::OpeningInProgress->value,
    ];

    public function index(TenderIndexRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $statusColumn = DB::raw('LOWER(LTRIM(RTRIM(Status)))');
            $typeColumn = DB::raw('LOWER(LTRIM(RTRIM(TenderType)))');
            $visibleStatusKeys = $this->normalizeStatusKeys([
                TenderStatusEnum::Published,
                TenderStatusEnum::OpeningInProgress,
            ]);
            $openTypeKeys = $this->normalizeTypeKeys([TenderTypeEnum::Open]);
            $restrictedTypeKeys = $this->normalizeTypeKeys([TenderTypeEnum::Restricted]);

            $query = Tender::query()->with([
                'procurementMode',
                'currency',
                'tenderCategoryRelation',
                'itemCategoryRelation',
                'documents',
            ]);

            $thirdPartyId = $validated['third_party_id'] ?? $this->resolveThirdPartyId($request);
            $supplierIds = $this->resolveSupplierIds($thirdPartyId);

            // Visible tenders:
            // 1) Open + visible statuses
            // 2) Restricted + invited (exists in t_TenderInvitations for any supplierId)
            $query->where(function ($vis) use ($supplierIds, $statusColumn, $typeColumn, $visibleStatusKeys, $openTypeKeys, $restrictedTypeKeys) {
                $vis->where(function ($open) use ($statusColumn, $typeColumn, $visibleStatusKeys, $openTypeKeys) {
                    $open->whereIn($typeColumn, $openTypeKeys)
                        ->whereIn($statusColumn, $visibleStatusKeys);
                });
                if (! empty($supplierIds)) {
                    $vis->orWhere(function ($restricted) use ($supplierIds, $statusColumn, $typeColumn, $visibleStatusKeys, $restrictedTypeKeys) {
                        $restricted->whereIn($typeColumn, $restrictedTypeKeys)
                            ->whereIn($statusColumn, $visibleStatusKeys)
                            ->where(function ($source) use ($supplierIds) {
                                // Prefer invitations source of truth
                                $source->whereExists(function ($sub) use ($supplierIds) {
                                    $sub->select(DB::raw(1))
                                        ->from('t_TenderInvitations as ti')
                                        ->whereColumn('ti.TenderId', 't_Tenders.Id')
                                        ->whereIn('ti.SupplierId', $supplierIds)
                                        ->whereNull('ti.DeletedOn');
                                })
                                    // Safety: if invitations are missing, fall back to selected suppliers (t_TenderSuppliers)
                                    ->orWhereExists(function ($sub2) use ($supplierIds) {
                                        $sub2->select(DB::raw(1))
                                            ->from('t_TenderSuppliers as ts')
                                            ->whereColumn('ts.TenderID', 't_Tenders.Id')
                                            ->whereIn('ts.SupplierID', $supplierIds)
                                            ->whereNull('ts.DeletedOn');
                                    });
                            });
                    });
                }
            });

            // Optional filters
            $statusParam = $validated['status'] ?? null;
            if (! empty($statusParam)) {
                $statusEnum = $this->normalizeTenderStatus($statusParam);
                if ($statusEnum) {
                    $statusKeys = $this->normalizeStatusKeys([$statusEnum]);
                    $query->whereIn($statusColumn, $statusKeys);
                }
            }

            $typeParam = $validated['tenderType'] ?? null;
            if (! empty($typeParam)) {
                $typeEnum = $this->tryNormalizeTenderType($typeParam);
                if ($typeEnum) {
                    $typeKeys = $this->normalizeTypeKeys([$typeEnum]);
                    $query->whereIn($typeColumn, $typeKeys);
                }
            }

            $search = trim((string)($validated['search'] ?? ''));
            if ($search !== '') {
                $like = '%' . str_replace(['%', '_'], ['[%]', '[_]'], $search) . '%';
                $query->where(function ($w) use ($like) {
                    $w->where('Title', 'like', $like)
                        ->orWhere('TenderNo', 'like', $like)
                        ->orWhere('ScopeOfWork', 'like', $like)
                        ->orWhere('Instructions', 'like', $like);
                });
            }

            $perPage = (int)($validated['per_page'] ?? 20);
            $page = (int)($validated['page'] ?? 1);
            $perPage = max(1, min(100, $perPage));

            $paginator = $query->orderByDesc('CreatedOn')
                ->orderByDesc('Id')
                ->paginate($perPage, ['*'], 'page', $page)
                ->appends($request->query());

            $activity = activity()->performedOn(new Tender());
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'viewed'])
                ->log('Viewed tenders list');

            return response()->json([
                'success' => true,
                'message' => 'Tenders retrieved successfully.',
                'data' => TenderResource::collection($paginator)->toArray($request),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->lastPage() > 0 ? $paginator->url($paginator->lastPage()) : null,
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve tenders', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tenders. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            // Get authenticated user for invitation checking
            $user = Auth::guard('sanctum')->user()
                ?? Auth::guard('third_party')->user()
                ?? Auth::user();
            $thirdPartyId = null;
            $supplierIds = [];

            if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
                $thirdPartyId = $user->ThirdPartyId;
            }

            $tender = Tender::with([
                'procurementMode',
                'currency',
                'tenderCategoryRelation',
                'itemCategoryRelation',
                'documents',
                'items.item.price',
            ])->find($id);

            if (! $tender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tender not found.',
                ], 404);
            }

            // Check if user has access to this tender
            $hasAccess = false;
            $invitationStatus = null;

            // Open tenders are accessible to all authenticated users if published
            if (
                $tender->TenderType === TenderTypeEnum::Open->value &&
                in_array($tender->Status, [TenderStatusEnum::Published->value, 'opening_in_progress'])
            ) {
                $hasAccess = true;
            }

            // For restricted tenders or to get invitation status, check invitations
            if ($thirdPartyId) {
                // Get supplier IDs for this third party
                $supplierIds = DB::table('t_Suppliers')
                    ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
                    ->where('t_SupplierMaster.ThirdPartyId', (int)$thirdPartyId)
                    ->whereNull('t_Suppliers.DeletedOn')
                    ->pluck('t_Suppliers.Id')
                    ->toArray();

                if (! empty($supplierIds)) {
                    // Check if supplier is invited
                    $invitation = DB::table('t_TenderInvitations')
                        ->whereIn('SupplierId', $supplierIds)
                        ->where('TenderId', $id)
                        ->whereNull('DeletedOn')
                        ->first();

                    if ($invitation) {
                        $hasAccess = true;
                        $invitationStatus = strtolower($invitation->ResponseStatus ?? 'pending');
                    }
                }
            }

            // If user doesn't have access and tender is restricted, return 403
            if (! $hasAccess && $tender->TenderType === TenderTypeEnum::Restricted->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this tender. This is a restricted tender and you have not been invited.',
                ], 403);
            }

            $invitationStatus = $this->resolveInvitationStatus($tender->Id, $supplierIds);

            return response()->json([
                'success' => true,
                'message' => 'Tender details retrieved successfully.',
                'data' => (new TenderResource($tender))->toArray($request),
                'invitation_status' => $invitationStatus,
            ], 200);
        } catch (Throwable $e) {
            Log::error('Error fetching tender details', [
                'tender_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tender details. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
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

            if (! empty($supplierIds)) {
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
    }

    private function canAccessTender(Tender $tender, array $supplierIds): bool
    {
        if ($tender->TenderType === TenderTypeEnum::Open->value) {
            return in_array($tender->Status, self::VISIBLE_STATUSES, true);
        }

        // For restricted tenders, check if any of the supplier IDs have an invitation
        if (empty($supplierIds)) {
            return false;
        }

        return DB::table('t_TenderInvitations')
            ->whereIn('SupplierId', $supplierIds)
            ->where('TenderId', $tender->Id)
            ->whereNull('DeletedOn')
            ->exists();
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

        $user = Auth::guard('sanctum')->user()
            ?? Auth::guard('third_party')->user()
            ?? Auth::user();

        if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
            return $user->ThirdPartyId;
        }

        return null;
    }

    public function store(StoreTenderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $tenderType = $this->normalizeTenderType($validated['tender_type']);
            $status = $this->normalizeTenderStatus($validated['status'] ?? null) ?? TenderStatusEnum::Draft;

            $tender = Tender::create([
                'TenderNo' => TenderService::ID() ?: 'TNDR-' . Str::upper(Str::random(8)),
                'Title' => $validated['title'],
                'TenderType' => $tenderType,
                'TenderCategory' => $validated['tender_category_id'],
                'ItemCategoryId' => $validated['item_category_id'],
                'ScopeOfWork' => $validated['scope_of_work'] ?? null,
                'Instructions' => $validated['instructions'] ?? null,
                'SubmissionDeadline' => $validated['submission_deadline'],
                'OpeningDate' => $validated['opening_date'],
                'Status' => $status,
                'ProcurementModeId' => $validated['procurement_mode_id'] ?? null,
                'StartDate' => $validated['start_date'] ?? null,
                'CurrencyId' => $validated['currency_id'],
                'ApprovalStatus' => null,
                'CreatedBy' => Auth::id() ?? null,
                'ModifiedBy' => Auth::id() ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tender created successfully.',
                'data' => (new TenderResource($tender))->toArray($request),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create tender', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create tender.',
            ], 500);
        }
    }

    public function update(UpdateTenderRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $tender = Tender::findOrFail($id);
            $update = [];

            if (array_key_exists('title', $validated)) {
                $update['Title'] = $validated['title'];
            }
            if (array_key_exists('tender_type', $validated)) {
                $update['TenderType'] = $this->normalizeTenderType($validated['tender_type']);
            }
            if (array_key_exists('tender_category_id', $validated)) {
                $update['TenderCategory'] = $validated['tender_category_id'];
            }
            if (array_key_exists('item_category_id', $validated)) {
                $update['ItemCategoryId'] = $validated['item_category_id'];
            }
            if (array_key_exists('submission_deadline', $validated)) {
                $update['SubmissionDeadline'] = $validated['submission_deadline'];
            }
            if (array_key_exists('opening_date', $validated)) {
                $update['OpeningDate'] = $validated['opening_date'];
            }
            if (array_key_exists('currency_id', $validated)) {
                $update['CurrencyId'] = $validated['currency_id'];
            }
            if (array_key_exists('scope_of_work', $validated)) {
                $update['ScopeOfWork'] = $validated['scope_of_work'];
            }
            if (array_key_exists('instructions', $validated)) {
                $update['Instructions'] = $validated['instructions'];
            }
            if (array_key_exists('procurement_mode_id', $validated)) {
                $update['ProcurementModeId'] = $validated['procurement_mode_id'];
            }
            if (array_key_exists('start_date', $validated)) {
                $update['StartDate'] = $validated['start_date'];
            }
            if (array_key_exists('status', $validated)) {
                $status = $this->normalizeTenderStatus($validated['status']);
                if ($status) {
                    $update['Status'] = $status;
                }
            }

            $update['ModifiedBy'] = Auth::id() ?? null;
            $update['ModifiedOn'] = now();

            $tender->update($update);

            return response()->json([
                'success' => true,
                'message' => 'Tender updated successfully.',
                'data' => (new TenderResource($tender->fresh()))->toArray($request),
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Failed to update tender', ['tender_id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update tender.',
            ], 500);
        }
    }

    public function addItem(AddTenderItemRequest $request, string $tenderId): JsonResponse
    {
        $validated = $request->validated();

        try {
            $tender = Tender::findOrFail($tenderId);

            $itemCategoryId = $validated['item_category_id'] ?? $tender->ItemCategoryId;
            if (empty($itemCategoryId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'item_category_id is required when tender has no ItemCategoryId.',
                ], 422);
            }

            $sourceType = $validated['source_type'] ?? 'MANUAL';

            $item = TenderItems::create([
                'TenderID' => $tender->Id,
                'SourceType' => $sourceType,
                'ItemID' => $validated['item_id'] ?? null,
                'PlanItemID' => $validated['plan_item_id'] ?? null,
                'ManualItemDescription' => $validated['manual_description'] ?? null,
                'PlannedQty' => null,
                'QtyToTender' => $validated['qty_to_tender'],
                'ItemCategory' => $itemCategoryId,
                'Remarks' => $validated['remarks'] ?? null,
                'CreatedBy' => Auth::id() ?? null,
                'ModifiedBy' => Auth::id() ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tender item added successfully.',
                'data' => (new TenderItemResource($item))->toArray($request),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to add tender item', ['tender_id' => $tenderId, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add tender item.',
            ], 500);
        }
    }

    public function deleteItem(string $tenderId, string $itemId): JsonResponse
    {
        try {
            $item = TenderItems::where('TenderID', $tenderId)->where('Id', $itemId)->first();
            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found',
                ], 404);
            }

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tender item deleted successfully.',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Failed to delete tender item', ['tender_id' => $tenderId, 'item_id' => $itemId, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tender item.',
            ], 500);
        }
    }

    private function normalizeTenderType(string $value): TenderTypeEnum
    {
        $normalized = strtolower(trim($value));

        $map = [
            'open' => TenderTypeEnum::Open->value,
            'restricted' => TenderTypeEnum::Restricted->value,
            TenderTypeEnum::Open->value => TenderTypeEnum::Open->value,
            TenderTypeEnum::Restricted->value => TenderTypeEnum::Restricted->value,
        ];

        $code = $map[$normalized] ?? $value;

        return TenderTypeEnum::from($code);
    }

    private function normalizeTenderStatus(?string $value): ?TenderStatusEnum
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = strtolower(trim($value));
        $map = [
            'published' => TenderStatusEnum::Published->value,
            'draft' => TenderStatusEnum::Draft->value,
            'closed' => TenderStatusEnum::Closed->value,
            'awarded' => TenderStatusEnum::Awarded->value,
            'openinginprogress' => TenderStatusEnum::OpeningInProgress->value,
            'opening_in_progress' => TenderStatusEnum::OpeningInProgress->value,
            TenderStatusEnum::Published->value => TenderStatusEnum::Published->value,
            TenderStatusEnum::Draft->value => TenderStatusEnum::Draft->value,
            TenderStatusEnum::Closed->value => TenderStatusEnum::Closed->value,
            TenderStatusEnum::Awarded->value => TenderStatusEnum::Awarded->value,
            TenderStatusEnum::OpeningInProgress->value => TenderStatusEnum::OpeningInProgress->value,
        ];

        $code = $map[$normalized] ?? $value;

        try {
            return TenderStatusEnum::from($code);
        } catch (\Throwable) {
            return null;
        }
    }

    private function tryNormalizeTenderType(?string $value): ?TenderTypeEnum
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = strtolower(trim($value));
        $map = [
            'open' => TenderTypeEnum::Open,
            'open tender' => TenderTypeEnum::Open,
            'restricted' => TenderTypeEnum::Restricted,
            'restricted tender' => TenderTypeEnum::Restricted,
            TenderTypeEnum::Open->value => TenderTypeEnum::Open,
            TenderTypeEnum::Restricted->value => TenderTypeEnum::Restricted,
        ];

        if (array_key_exists($normalized, $map)) {
            return $map[$normalized];
        }

        return TenderTypeEnum::tryFrom($value);
    }

    private function normalizeStatusKeys(array $values): array
    {
        $keys = [];

        foreach ($values as $value) {
            if ($value instanceof TenderStatusEnum) {
                $keys[] = $value->value;
                $keys[] = $value->name;
                $keys[] = $value->displayName();
            } elseif (is_string($value) && trim($value) !== '') {
                $keys[] = $value;
            }
        }

        return $this->normalizeKeyVariants($keys);
    }

    private function normalizeTypeKeys(array $values): array
    {
        $keys = [];

        foreach ($values as $value) {
            if ($value instanceof TenderTypeEnum) {
                $keys[] = $value->value;
                $keys[] = $value->name;
                $keys[] = $value->displayName();
            } elseif (is_string($value) && trim($value) !== '') {
                $keys[] = $value;
            }
        }

        return $this->normalizeKeyVariants($keys);
    }

    private function normalizeKeyVariants(array $values): array
    {
        $normalized = [];

        foreach ($values as $value) {
            $base = strtolower(trim((string)$value));
            if ($base === '') {
                continue;
            }

            $normalized[] = $base;
            $normalized[] = str_replace(' ', '', $base);
            $normalized[] = str_replace(' ', '_', $base);
        }

        return array_values(array_unique($normalized));
    }

    private function resolveSupplierIds(?int $thirdPartyId): array
    {
        if (! $thirdPartyId) {
            return [];
        }

        return DB::table('t_Suppliers')
            ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
            ->where('t_SupplierMaster.ThirdPartyId', $thirdPartyId)
            ->whereNull('t_Suppliers.DeletedOn')
            ->pluck('t_Suppliers.Id')
            ->unique()
            ->values()
            ->all();
    }

    public function addSupplier(AddTenderSupplierRequest $request, string $tenderId): JsonResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $tender = Tender::findOrFail($tenderId);

            $tenderSupplier = TenderSupplier::create([
                'TenderID' => $tenderId,
                'SupplierID' => $validated['supplier_id'],
                'CreatedBy' => Auth::id() ?? null,
                'ModifiedBy' => Auth::id() ?? null,
            ]);

            DB::commit();

            $activity = activity()->performedOn($tenderSupplier);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'create'])
                ->log('Tender Supplier added successfully to Tender ID: ' . $tenderId);

            return response()->json([
                'success' => true,
                'message' => 'Tender supplier added successfully.',
                'supplier' => (new TenderSupplierResource($tenderSupplier))->toArray($request),
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- ADD TENDER SUPPLIER ERROR --- " . $e->getMessage());
            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add tender supplier. Please try again.',
            ], 500);
        }
    }

    public function deleteSupplier(string $tenderId, string $supplierId): JsonResponse
    {
        try {
            DB::beginTransaction();
            $tenderSupplier = TenderSupplier::where('TenderID', $tenderId)->findOrFail($supplierId);
            $tenderSupplier->delete();

            DB::commit();

            $activity = activity()->performedOn($tenderSupplier);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'delete'])
                ->log('Tender Supplier deleted successfully from Tender ID: ' . $tenderId);

            return response()->json([
                'success' => true,
                'message' => 'Tender supplier deleted successfully.',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- DELETE TENDER SUPPLIER ERROR --- " . $e->getMessage());
            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tender supplier. Please try again.',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $tender = Tender::findOrFail($id);
            $tender->delete();
            $activity = activity()->performedOn($tender);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'delete'])
                ->log('Tender deleted successfully with ID: ' . $id);

            return response()->json([
                'success' => true,
                'message' => 'Tender deleted successfully.',
            ], 200);
        } catch (Throwable $th) {
            Log::error("--- DELETE TENDER ERROR --- " . $th->getMessage());
            Log::error($th);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tender. Please try again.',
            ], 500);
        }
    }

    protected function generateTenderStages(Tender $tender, $procurementModeId, $startDate)
    {
        $timelineStages = ModeTimeline::where('ProcurementModeId', $procurementModeId)->get();
        $startDate = Carbon::parse($startDate);

        foreach ($timelineStages as $stage) {
            $endDate = (clone $startDate)->addDays($stage->DurationDays - 1);

            TenderStage::create([
                'TenderId' => $tender->Id,
                'Stage' => $stage->Stage,
                'DurationDays' => $stage->DurationDays,
                'StartDate' => $startDate->toDateString(),
                'EndDate' => $endDate->toDateString(),
                'CreatedBy' => Auth::id() ?? null,
                'ModifiedBy' => Auth::id() ?? null,
            ]);
            $startDate = (clone $endDate)->addDay();
        }
    }
}
