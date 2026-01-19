<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\ModeTimeline;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderItems;
use App\Models\Procurement\TenderStage;
use App\Models\Procurement\TenderSupplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Throwable;

class TenderApiController extends Controller
{
    private const VISIBLE_STATUSES = [
        TenderStatusEnum::Published->value,
        TenderStatusEnum::OpeningInProgress->value,
    ];

    private const SOURCE_PLAN = 'PLAN';
    private const SOURCE_MANUAL = 'MANUAL';
    private const SOURCE_MANUAL_DESCRIPTION = 'MANUAL_DESCRIPTION';

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

            $enforceInvites = filter_var($request->query('enforce_invites', true), FILTER_VALIDATE_BOOLEAN);
            $thirdPartyId = $this->resolveThirdPartyId($request);

            if ($enforceInvites) {
                $supplierIds = $this->resolveSupplierIds($thirdPartyId);
                $this->applyVisibilityScope($query, $supplierIds);
            } else {
                $query->whereIn('Status', self::VISIBLE_STATUSES);
            }

            $this->applyOptionalFilters($query, $request);

            $tenders = $query
                ->orderByDesc('CreatedOn')
                ->orderByDesc('Id')
                ->get();

            activity()
                ->performedOn(new Tender())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'viewed'])
                ->log('Viewed tenders list');

            return response()->json([
                'message' => 'Tenders retrieved successfully.',
                'data' => $tenders,
            ]);
        } catch (Throwable $e) {
            Log::error('Tender index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve tenders. Please try again.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tender_type' => ['required', new Enum(TenderTypeEnum::class)],
            'tender_category_id' => 'required|exists:t_TenderCategories,Id',
            'item_category_id' => 'required|exists:item_categories,Id',
            'procurement_mode_id' => 'required|exists:t_ProcurementModes,Id',
            'currency_id' => 'required|exists:t_Currencies,Id',
            'scope_of_work' => 'required|string',
            'instructions' => 'required|string',
            'submission_deadline' => 'required|date|after_or_equal:today',
            'opening_date' => 'required|date|after_or_equal:submission_deadline',
            'plan_items' => 'nullable|array',
            'plan_items.*.item_id' => 'nullable|integer|exists:t_ItemMasterLists,Id',
            'plan_items.*.qty' => 'required_with:plan_items.*.item_id|integer|min:1',
            'plan_items.*.pr_ref' => 'nullable|string|max:255',
            'manual_items' => 'nullable|array',
            'manual_items.*.item_id' => 'nullable|integer|exists:t_ItemMasterLists,Id',
            'manual_items.*.qty' => 'required_with:manual_items.*.item_id,manual_items.*.manual_item_description|integer|min:1',
            'manual_items.*.pr_ref' => 'nullable|string|max:255',
            'manual_items.*.manual_item_description' => 'required_without:manual_items.*.item_id|nullable|string|max:255',
            'suppliers' => 'nullable|array',
            'suppliers.*' => 'integer|exists:t_Suppliers,Id',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2048',
        ]);

        try {
            $tenderId = DB::transaction(function () use ($validated) {
                $now = now();
                $actorId = Auth::id();

                $tender = new Tender();
                $tender->TenderNo = 'TNDR-' . Str::upper(Str::random(8));
                $tender->Title = $validated['title'];
                $tender->TenderType = TenderTypeEnum::from($validated['tender_type'])->value;
                $tender->TenderCategory = $validated['tender_category_id'];
                $tender->ScopeOfWork = $validated['scope_of_work'];
                $tender->Instructions = $validated['instructions'];
                $tender->SubmissionDeadline = $validated['submission_deadline'];
                $tender->OpeningDate = $validated['opening_date'];
                $tender->Status = 'dr';
                $tender->ProcurementModeId = $validated['procurement_mode_id'];
                $tender->EstimatedValue = null;
                $tender->ItemCategoryId = $validated['item_category_id'];
                $tender->CurrencyId = $validated['currency_id'];
                $tender->CreatedBy = $actorId;
                $tender->CreatedOn = $now;
                $tender->ModifiedBy = $actorId;
                $tender->ModifiedOn = $now;
                $tender->save();

                $tenderId = (int) $tender->Id;

                $this->createPlanItems($tenderId, $validated);
                $this->createManualItems($tenderId, $validated, (int) $tender->ItemCategoryId);
                $this->attachSuppliers($tenderId, $validated);
                $this->generateTenderStages($tender, (int) $validated['procurement_mode_id'], $tender->CreatedOn);

                return $tenderId;
            });

            activity()
                ->performedOn((new Tender())->setAttribute('Id', $tenderId))
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Tender created successfully with ID: ' . $tenderId);

            return response()->json([
                'message' => 'Tender created successfully.',
                'tender_id' => $tenderId,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Create tender failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to create tender. Please try again.',
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tender_category_id' => 'required|exists:t_TenderCategories,Id',
            'currency_id' => 'required|exists:t_Currencies,Id',
            'scope_of_work' => 'required|string',
            'instructions' => 'required|string',
            'submission_deadline' => 'required|date|after_or_equal:today',
            'opening_date' => 'required|date|after_or_equal:submission_deadline',
            'procurement_mode_id' => 'required|exists:t_ProcurementModes,Id',
            'item_category_id' => 'required|exists:item_categories,Id',
        ]);

        try {
            DB::transaction(function () use ($validated, $id) {
                $tender = Tender::query()->findOrFail($id);
                $tender->Title = $validated['title'];
                $tender->TenderCategory = $validated['tender_category_id'];
                $tender->CurrencyId = $validated['currency_id'];
                $tender->ScopeOfWork = $validated['scope_of_work'];
                $tender->Instructions = $validated['instructions'];
                $tender->SubmissionDeadline = $validated['submission_deadline'];
                $tender->OpeningDate = $validated['opening_date'];
                $tender->ProcurementModeId = $validated['procurement_mode_id'];
                $tender->ItemCategoryId = $validated['item_category_id'];
                $tender->ModifiedBy = Auth::id();
                $tender->ModifiedOn = now();
                $tender->save();

                activity()
                    ->performedOn($tender)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'update'])
                    ->log('Tender updated successfully with ID: ' . $id);
            });

            return response()->json([
                'message' => 'Tender updated successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error('Update tender failed', [
                'tender_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to update tender. Please try again.',
            ], 500);
        }
    }

    public function addItem(Request $request, string $tenderId): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'nullable|integer|exists:t_ItemMasterLists,Id',
            'qty_to_tender' => 'required_without:manual_item_description|integer|min:1',
            'pr_ref' => 'nullable|string|max:255',
            'manual_item_description' => 'required_without:item_id|nullable|string|max:255',
        ]);

        try {
            $tenderItem = DB::transaction(function () use ($validated, $tenderId) {
                $tender = Tender::query()->findOrFail($tenderId);

                return TenderItems::create([
                    'TenderID' => (int) $tenderId,
                    'SourceType' => ($validated['item_id'] ?? null) ? self::SOURCE_MANUAL : self::SOURCE_MANUAL_DESCRIPTION,
                    'ItemID' => $validated['item_id'] ?? null,
                    'ManualItemDescription' => $validated['manual_item_description'] ?? null,
                    'PlannedQty' => null,
                    'QtyToTender' => (int) $validated['qty_to_tender'],
                    'ItemCategory' => $tender->ItemCategoryId,
                    'Remarks' => null,
                    'RelatedPRID' => $validated['pr_ref'] ?? null,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
            });

            activity()
                ->performedOn($tenderItem)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Tender Item added successfully to Tender ID: ' . $tenderId);

            return response()->json([
                'message' => 'Tender item added successfully.',
                'item' => $tenderItem,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Add tender item failed', [
                'tender_id' => $tenderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to add tender item. Please try again.',
            ], 500);
        }
    }

    public function deleteItem(string $tenderId, string $itemId): JsonResponse
    {
        try {
            DB::transaction(function () use ($tenderId, $itemId) {
                $tenderItem = TenderItems::query()
                    ->where('TenderID', (int) $tenderId)
                    ->where('Id', (int) $itemId)
                    ->firstOrFail();

                $tenderItem->delete();

                activity()
                    ->performedOn($tenderItem)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'delete'])
                    ->log('Tender Item deleted successfully from Tender ID: ' . $tenderId);
            });

            return response()->json([
                'message' => 'Tender item deleted successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error('Delete tender item failed', [
                'tender_id' => $tenderId,
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to delete tender item. Please try again.',
            ], 500);
        }
    }

    public function addSupplier(Request $request, string $tenderId): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|integer|exists:t_Suppliers,Id',
        ]);

        try {
            $tenderSupplier = DB::transaction(function () use ($validated, $tenderId) {
                Tender::query()->findOrFail($tenderId);

                return TenderSupplier::create([
                    'TenderID' => (int) $tenderId,
                    'SupplierID' => (int) $validated['supplier_id'],
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
            });

            activity()
                ->performedOn($tenderSupplier)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Tender Supplier added successfully to Tender ID: ' . $tenderId);

            return response()->json([
                'message' => 'Tender supplier added successfully.',
                'supplier' => $tenderSupplier,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Add tender supplier failed', [
                'tender_id' => $tenderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to add tender supplier. Please try again.',
            ], 500);
        }
    }

    public function deleteSupplier(string $tenderId, string $supplierId): JsonResponse
    {
        try {
            DB::transaction(function () use ($tenderId, $supplierId) {
                $tenderSupplier = TenderSupplier::query()
                    ->where('TenderID', (int) $tenderId)
                    ->where('SupplierID', (int) $supplierId)
                    ->firstOrFail();

                $tenderSupplier->delete();

                activity()
                    ->performedOn($tenderSupplier)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'delete'])
                    ->log('Tender Supplier deleted successfully from Tender ID: ' . $tenderId);
            });

            return response()->json([
                'message' => 'Tender supplier deleted successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error('Delete tender supplier failed', [
                'tender_id' => $tenderId,
                'supplier_id' => $supplierId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to delete tender supplier. Please try again.',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $tender = Tender::query()->findOrFail($id);
                $tender->delete();

                activity()
                    ->performedOn($tender)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'delete'])
                    ->log('Tender deleted successfully with ID: ' . $id);
            });

            return response()->json([
                'message' => 'Tender deleted successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error('Delete tender failed', [
                'tender_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to delete tender. Please try again.',
            ], 500);
        }
    }

    protected function generateTenderStages(Tender $tender, int $procurementModeId, $startDate): void
    {
        $timelineStages = ModeTimeline::query()
            ->where('ProcurementModeId', $procurementModeId)
            ->orderBy('Id')
            ->get();

        $cursor = Carbon::parse($startDate);

        foreach ($timelineStages as $stage) {
            $duration = (int) $stage->DurationDays;
            $endDate = (clone $cursor)->addDays(max(1, $duration) - 1);

            TenderStage::create([
                'TenderId' => (int) $tender->Id,
                'Stage' => $stage->Stage,
                'DurationDays' => $duration,
                'StartDate' => $cursor->toDateString(),
                'EndDate' => $endDate->toDateString(),
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            $cursor = (clone $endDate)->addDay();
        }
    }

    private function resolveThirdPartyId(Request $request): ?int
    {
        $thirdPartyId = $request->query('third_party_id');
        if (!empty($thirdPartyId)) {
            return (int) $thirdPartyId;
        }

        $user = Auth::guard('sanctum')->user();
        if ($user && property_exists($user, 'ThirdPartyId') && !empty($user->ThirdPartyId)) {
            return (int) $user->ThirdPartyId;
        }

        if ($user && method_exists($user, 'thirdParty') && $user->thirdParty) {
            $tpId = $user->thirdParty->Id ?? null;
            return $tpId ? (int) $tpId : null;
        }

        return null;
    }

    private function resolveSupplierIds(?int $thirdPartyId): array
    {
        if (!$thirdPartyId) {
            return [];
        }

        $ids = DB::table('t_Suppliers')
            ->join('t_SupplierMaster', 't_Suppliers.SupplierMasterId', '=', 't_SupplierMaster.Id')
            ->where('t_SupplierMaster.ThirdPartyId', $thirdPartyId)
            ->whereNull('t_Suppliers.DeletedOn')
            ->pluck('t_Suppliers.Id')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        return $ids;
    }

    private function applyVisibilityScope(Builder $query, array $supplierIds): void
    {
        $query->where(function (Builder $vis) use ($supplierIds) {
            $vis->where(function (Builder $open) {
                $open->where('TenderType', TenderTypeEnum::Open->value)
                    ->whereIn('Status', self::VISIBLE_STATUSES);
            });

            if (!empty($supplierIds)) {
                $vis->orWhere(function (Builder $restricted) use ($supplierIds) {
                    $restricted->where('TenderType', TenderTypeEnum::Restricted->value)
                        ->whereIn('Status', self::VISIBLE_STATUSES)
                        ->where(function (Builder $source) use ($supplierIds) {
                            $source->whereExists(function ($sub) use ($supplierIds) {
                                $sub->select(DB::raw(1))
                                    ->from('t_TenderInvitations as ti')
                                    ->whereColumn('ti.TenderId', 't_Tenders.Id')
                                    ->whereIn('ti.SupplierId', $supplierIds)
                                    ->whereNull('ti.DeletedOn');
                            })->orWhereExists(function ($sub2) use ($supplierIds) {
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
    }

    private function applyOptionalFilters(Builder $query, Request $request): void
    {
        $status = $request->query('status');
        if (!empty($status)) {
            $query->where('Status', $status);
        }

        $type = $request->query('tenderType');
        if (!empty($type)) {
            $query->where('TenderType', $type);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['[%]', '[_]'], $search) . '%';

            $query->where(function (Builder $w) use ($like) {
                $w->where('Title', 'like', $like)
                    ->orWhere('TenderNo', 'like', $like)
                    ->orWhere('ScopeOfWork', 'like', $like)
                    ->orWhere('Instructions', 'like', $like);
            });
        }
    }

    private function createPlanItems(int $tenderId, array $validated): void
    {
        $items = $validated['plan_items'] ?? null;
        if (empty($items) || !is_array($items)) {
            return;
        }

        $actorId = Auth::id();

        foreach ($items as $compositeKey => $item) {
            $split = explode('-', (string) $compositeKey);
            $planItemId = (int) end($split);

            TenderItems::create([
                'TenderID' => $tenderId,
                'SourceType' => self::SOURCE_PLAN,
                'ItemID' => $item['item_id'] ?? null,
                'PlanItemID' => $planItemId ?: null,
                'PlannedQty' => (int) $item['qty'],
                'QtyToTender' => (int) $item['qty'],
                'ItemCategory' => (int) $validated['item_category_id'],
                'Remarks' => null,
                'RelatedPRID' => $item['pr_ref'] ?? null,
                'CreatedBy' => $actorId,
                'ModifiedBy' => $actorId,
            ]);
        }
    }

    private function createManualItems(int $tenderId, array $validated, int $itemCategoryId): void
    {
        $items = $validated['manual_items'] ?? null;
        if (empty($items) || !is_array($items)) {
            return;
        }

        $actorId = Auth::id();

        foreach ($items as $manualItem) {
            $itemId = $manualItem['item_id'] ?? null;
            $desc = $manualItem['manual_item_description'] ?? null;

            if (empty($itemId) && empty($desc)) {
                continue;
            }

            TenderItems::create([
                'TenderID' => $tenderId,
                'SourceType' => self::SOURCE_MANUAL,
                'ItemID' => $itemId ?: null,
                'ManualItemDescription' => $desc ?: null,
                'PlannedQty' => null,
                'QtyToTender' => (int) $manualItem['qty'],
                'ItemCategory' => $itemCategoryId,
                'Remarks' => null,
                'RelatedPRID' => $manualItem['pr_ref'] ?? null,
                'CreatedBy' => $actorId,
                'ModifiedBy' => $actorId,
            ]);
        }
    }

    private function attachSuppliers(int $tenderId, array $validated): void
    {
        $suppliers = $validated['suppliers'] ?? null;
        if (empty($suppliers) || !is_array($suppliers)) {
            return;
        }

        $actorId = Auth::id();

        foreach ($suppliers as $supplierId) {
            TenderSupplier::create([
                'TenderID' => $tenderId,
                'SupplierID' => (int) $supplierId,
                'CreatedBy' => $actorId,
                'ModifiedBy' => $actorId,
            ]);
        }
    }
}
