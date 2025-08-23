<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\TenderTypeEnum;
use App\Enums\TenderStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\ModeTimeline;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderItems;
use App\Models\Procurement\TenderStage;
use App\Models\Procurement\TenderSupplier;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Throwable;


class TenderApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Tender::with(['procurementMode', 'currency', 'tenderCategoryRelation', 'itemCategoryRelation']);

            $status = $request->query('status');
            if ($status) {
                switch (strtolower($status)) {
                    case 'open':
                        $query->where('Status', TenderStatusEnum::Published);
                        break;
                    case 'closed':
                        $query->where('Status', TenderStatusEnum::Closed);
                        break;
                    default:
                        $query->where('Status', TenderStatusEnum::Draft);
                        break;
                }
            }

            $tenders = $query->get();

            $activity = activity()->performedOn(new Tender());
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'viewed'])
                ->log('Viewed tenders list');
            return response()->json([
                'message' => 'Tenders retrieved successfully.',
                'data' => $tenders
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to retrieve tenders. Please try again.'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $tender = new Tender();
            $tender->TenderNo = 'TNDR-' . Str::upper(Str::random(8));
            $tender->Title = $validated['title'];
            $tender->TenderType = TenderTypeEnum::from($validated['tender_type']);
            $tender->TenderCategory = $validated['tender_category_id'];
            $tender->ScopeOfWork = $validated['scope_of_work'];
            $tender->Instructions = $validated['instructions'];
            $tender->SubmissionDeadline = $validated['submission_deadline'];
            $tender->OpeningDate = $validated['opening_date'];
            $tender->Status = 'dr';
            $tender->ProcurementModeId = $validated['procurement_mode_id'];
            $tender->EstimatedValue = null;
            $tender->ItemCategoryId = $validated['item_category_id'];
            $tender->CreatedBy = Auth::id() ?? null;
            $tender->CreatedOn = now();
            $tender->ModifiedBy = Auth::id() ?? null;
            $tender->ModifiedOn = now();
            $tender->CurrencyId = $validated['currency_id'];
            $tender->save();

            $tenderId = $tender->Id;

            if (!empty($validated['plan_items'])) {
                foreach ($validated['plan_items'] as $compositeKey => $item) {
                    $split = explode('-', $compositeKey);
                    $planItemId = (int)end($split);
                    TenderItems::create([
                        'TenderID' => $tenderId,
                        'SourceType' => 'PLAN',
                        'ItemID' => $item['item_id'] ?? null,
                        'PlanItemID' => $planItemId,
                        'PlannedQty' => $item['qty'],
                        'QtyToTender' => $item['qty'],
                        'ItemCategory' => $validated['item_category_id'],
                        'Remarks' => null,
                        'RelatedPRID' => $item['pr_ref'] ?? null,
                        'CreatedBy' => Auth::id() ?? null,
                        'ModifiedBy' => Auth::id() ?? null,
                    ]);
                }
            }

            if (!empty($validated['manual_items'])) {
                foreach ($validated['manual_items'] as $manualItem) {
                    if (empty($manualItem['item_id']) && empty($manualItem['manual_item_description'])) {
                        continue;
                    }
                    TenderItems::create([
                        'TenderID' => $tenderId,
                        'SourceType' => 'MANUAL',
                        'ItemID' => $manualItem['item_id'] ?? null,
                        'ManualItemDescription' => $manualItem['manual_item_description'] ?? null,
                        'PlannedQty' => null,
                        'QtyToTender' => $manualItem['qty'],
                        'ItemCategory' => $validated['item_category_id'],
                        'Remarks' => null,
                        'RelatedPRID' => $manualItem['pr_ref'] ?? null,
                        'CreatedBy' => Auth::id() ?? null,
                        'ModifiedBy' => Auth::id() ?? null,
                    ]);
                }
            }

            if (!empty($validated['suppliers'])) {
                foreach ($validated['suppliers'] as $supplierId) {
                    TenderSupplier::create([
                        'TenderID' => $tenderId,
                        'SupplierID' => $supplierId,
                        'CreatedBy' => Auth::id() ?? null,
                        'ModifiedBy' => Auth::id() ?? null,
                    ]);
                }
            }

            $this->generateTenderStages($tender, $validated['procurement_mode_id'], $tender->CreatedOn);

            DB::commit();

            $activity = activity()->performedOn($tender);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'create'])
                ->log('Tender created successfully with ID: ' . $tenderId);

            return response()->json([
                'message' => 'Tender created successfully.',
                'tender_id' => $tenderId
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- CREATE TENDER ERROR --- " . $e->getMessage());
            Log::error($e);
            return response()->json(['message' => 'Failed to create tender. Please try again.'], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $tender = Tender::findOrFail($id);
            $tender->Title = $validated['title'];
            $tender->TenderCategory = $validated['tender_category_id'];
            $tender->CurrencyId = $validated['currency_id'];
            $tender->ScopeOfWork = $validated['scope_of_work'];
            $tender->Instructions = $validated['instructions'];
            $tender->SubmissionDeadline = $validated['submission_deadline'];
            $tender->OpeningDate = $validated['opening_date'];
            $tender->ProcurementModeId = $validated['procurement_mode_id'];
            $tender->ItemCategoryId = $validated['item_category_id'];
            $tender->ModifiedBy = Auth::id() ?? null;
            $tender->save();

            DB::commit();

            $activity = activity()->performedOn($tender);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'update'])
                ->log('Tender updated successfully with ID: ' . $id);

            return response()->json(['message' => 'Tender updated successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('--- UPDATE TENDER ERROR --- ' . $e->getMessage());
            Log::error($e);
            return response()->json(['message' => 'Failed to update tender. Please try again.'], 500);
        }
    }

    public function addItem(Request $request, string $tenderId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'item_id' => 'nullable|integer|exists:t_ItemMasterLists,Id',
                'qty_to_tender' => 'required_without:manual_item_description|integer|min:1',
                'pr_ref' => 'nullable|string|max:255',
                'manual_item_description' => 'required_without:item_id|nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $tender = Tender::findOrFail($tenderId);

            $tenderItem = TenderItems::create([
                'TenderID' => $tenderId,
                'SourceType' => ($validated['item_id'] ?? null) ? 'MANUAL' : 'MANUAL_DESCRIPTION',
                'ItemID' => $validated['item_id'] ?? null,
                'ManualItemDescription' => $validated['manual_item_description'] ?? null,
                'PlannedQty' => null,
                'QtyToTender' => $validated['qty_to_tender'],
                'ItemCategory' => $tender->ItemCategoryId,
                'Remarks' => null,
                'RelatedPRID' => $validated['pr_ref'] ?? null,
                'CreatedBy' => Auth::id() ?? null,
                'ModifiedBy' => Auth::id() ?? null,
            ]);

            DB::commit();

            $activity = activity()->performedOn($tenderItem);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'create'])
                ->log('Tender Item added successfully to Tender ID: ' . $tenderId);

            return response()->json(['message' => 'Tender item added successfully.', 'item' => $tenderItem], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- ADD TENDER ITEM ERROR --- " . $e->getMessage());
            Log::error($e);
            return response()->json(['message' => 'Failed to add tender item. Please try again.'], 500);
        }
    }

    public function deleteItem(string $tenderId, string $itemId): JsonResponse
    {
        try {
            DB::beginTransaction();
            $tenderItem = TenderItems::where('TenderID', $tenderId)->findOrFail($itemId);
            $tenderItem->delete();

            DB::commit();

            $activity = activity()->performedOn($tenderItem);
            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            } else {
                $activity->causedBy(null);
            }
            $activity->withProperties(['action' => 'delete'])
                ->log('Tender Item deleted successfully from Tender ID: ' . $tenderId);

            return response()->json(['message' => 'Tender item deleted successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- DELETE TENDER ITEM ERROR --- " . $e->getMessage());
            Log::error($e);
            return response()->json(['message' => 'Failed to delete tender item. Please try again.'], 500);
        }
    }

    public function addSupplier(Request $request, string $tenderId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|integer|exists:t_Suppliers,Id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

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

            return response()->json(['message' => 'Tender supplier added successfully.', 'supplier' => $tenderSupplier], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- ADD TENDER SUPPLIER ERROR --- " . $e->getMessage());
            Log::error($e);
            return response()->json(['message' => 'Failed to add tender supplier. Please try again.'], 500);
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

            return response()->json(['message' => 'Tender supplier deleted successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("--- DELETE TENDER SUPPLIER ERROR --- " . $e->getMessage());
            Log::error($e);
            return response()->json(['message' => 'Failed to delete tender supplier. Please try again.'], 500);
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
            return response()->json(['message' => 'Tender deleted successfully.'], 200);
        } catch (Throwable $th) {
            Log::error("--- DELETE TENDER ERROR --- " . $th->getMessage());
            Log::error($th);
            return response()->json(['message' => 'Failed to delete tender. Please try again.'], 500);
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
