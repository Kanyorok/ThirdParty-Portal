<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Requisition\RequisitionItemRequest;
use App\Models\Procurement\RequisitionLine;
use App\Models\Procurement\Requisitions;
use App\Services\Procurement\Items\ItemService;
use App\Services\Procurement\Requisition\RequisitionItemService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RequisitionItemsController extends Controller
{
    public function __construct(protected RequisitionItemService $service, protected ItemService $itemService)
    {
        $this->middleware('ajax')->except(['index', 'create', 'show']);
    }

    /**
     * Get items by type - returns items from plan if available, otherwise items by type or all items
     */
    public function getItems(Request $request, $type = null): JsonResponse
    {
        try {
            $requisitionId = $request->query('requisition_id');

            Log::info('getItems called', [
                'type' => $type,
                'requisition_id' => $requisitionId,
            ]);

            $planRef = null;
            $items = collect([]);

            // Get plan reference if requisition exists
            if ($requisitionId) {
                $requisition = DB::table('t_Requisitions')
                    ->where('Id', $requisitionId)
                    ->first();

                $planRef = $requisition->PlanRef ?? null;

                Log::info('Requisition details', [
                    'requisition_id' => $requisitionId,
                    'plan_ref' => $planRef,
                ]);
            }

            // If we have a plan, get plan-specific items with availability
            if ($planRef) {
                $items = $this->getPlanAvailableItems($planRef);

                Log::info('Plan items result', [
                    'plan_id' => $planRef,
                    'count' => $items->count(),
                ]);
            } else {
                // No plan, get items by type or all items
                if ($type && $type !== 'all') {
                    Log::info('Fetching items by type (no plan)', [
                        'type' => $type,
                    ]);

                    $items = $this->getGenericItemsByType($type);
                } else {
                    Log::info('Fetching all items (no plan, no type filter)');

                    $items = $this->getGenericItems();
                }
            }

            Log::info('Final items to return', [
                'count' => $items->count(),
                'sample' => $items->first(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $items->values()->toArray(), // Ensure array keys are sequential
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch items', [
                'type' => $type,
                'requisition_id' => $request->query('requisition_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get item details including price, UOM, and category
     */
    public function getItemDetails(Request $request, $item): JsonResponse
    {
        if (empty($item)) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $requisitionId = $request->query('requisition_id');
        $planId = null;

        if ($requisitionId) {
            $planId = DB::table('t_Requisitions')->where('Id', $requisitionId)->value('PlanRef');
        }

        try {
            Log::info('Getting item details', [
                'item_id' => $item,
                'requisition_id' => $requisitionId,
                'plan_id' => $planId,
            ]);

            // First try to get plan-specific details
            if ($planId) {
                $planDetails = $this->getPlanItemDetails($item, $planId);

                if ($planDetails) {
                    Log::info('Returning plan-specific item details', [
                        'item_id' => $item,
                        'details' => $planDetails,
                    ]);

                    return response()->json([
                        'success' => true,
                        'data' => [$planDetails], // Return as array for consistency
                    ]);
                } else {
                    Log::warning('Plan item details not found', [
                        'item_id' => $item,
                        'plan_id' => $planId,
                    ]);
                }
            }

            // Fallback to generic item details
            $genericDetails = $this->getGenericItemDetails($item);

            Log::info('Returning generic item details', [
                'item_id' => $item,
                'details' => $genericDetails,
            ]);

            return response()->json([
                'success' => true,
                'data' => $genericDetails ? [$genericDetails] : [],
            ]);
        } catch (Exception $e) {
            Log::error('getItemDetails failed', [
                'item' => $item,
                'requisition_id' => $requisitionId,
                'plan_id' => $planId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Final safe fallback
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }

    /**
     * Get item details from procurement plan
     */
    private function getPlanItemDetails($itemId, $planId)
    {
        try {
            $planItems = DB::table('t_PlanLineItem as pli')
                ->join('t_Items as itm', 'pli.ItemID', '=', 'itm.Id')
                ->leftJoin('t_ItemCategories as cat', 'itm.Category', '=', 'cat.Id')
                ->leftJoin('t_UOM as uom', 'itm.UOM', '=', 'uom.Id') // Join UOM table
                ->where('pli.ItemID', $itemId)
                ->where('pli.PlanID', $planId)
                ->where('pli.IsDeleted', 0)
                ->whereNull('itm.DeletedOn')
                ->select(
                    'itm.Id',
                    'itm.ItemName as Name',
                    'pli.MergedQty as PlanQuantity',
                    'pli.UnitOfMeasure as UOM',
                    'itm.UOM as UOMID',
                    DB::raw('CASE WHEN pli.AdjustedCost > 0 THEN pli.AdjustedCost ELSE ISNULL(pli.EstimatedUnitCost, 0) END as UnitPrice'),
                    'itm.Category as CategoryId',
                    'cat.Name as CategoryName',
                    'pli.LineItemID'
                )
                ->get();

            foreach ($planItems as $item) {
                $usedQty = DB::table('t_RequisitionLines')
                    ->where('PlanLineRef', $item->LineItemID)
                    ->whereNull('DeletedOn')
                    ->sum('Quantity') ?? 0;

                $remainingQty = $item->PlanQuantity - $usedQty;

                if ($remainingQty > 0) {
                    $item->UsedQuantity = $usedQty;
                    $item->RemainingQty = $remainingQty;

                    return (array) $item;
                }
            }

            // If no item with remaining quantity found, return the first one (or null)
            if ($planItems->isNotEmpty()) {
                $item = $planItems->first();
                $usedQty = DB::table('t_RequisitionLines')
                    ->where('PlanLineRef', $item->LineItemID)
                    ->whereNull('DeletedOn')
                    ->sum('Quantity') ?? 0;
                $item->UsedQuantity = $usedQty;
                $item->RemainingQty = $item->PlanQuantity - $usedQty;

                return (array) $item;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get plan item details', [
                'item_id' => $itemId,
                'plan_id' => $planId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get generic item details (not from plan)
     */
    private function getGenericItemDetails($itemId)
    {
        try {
            $details = DB::table('t_Items as itm')
                ->leftJoin('t_UOM as uom', 'itm.UOM', '=', 'uom.Id')
                ->leftJoin('t_ItemCategories as cat', 'itm.Category', '=', 'cat.Id')
                ->leftJoin('t_Pricing as price', 'itm.ItemPrice', '=', 'price.Id')
                ->where('itm.Id', $itemId)
                ->whereNull('itm.DeletedOn')
                ->select(
                    DB::raw('ISNULL(uom.Code, \'Unit\') as UOM'),
                    'itm.UOM as UOMID',
                    DB::raw('ISNULL(price.ActualPrice, 0) as UnitPrice'),
                    'itm.Category as CategoryId',
                    'cat.Name as CategoryName',
                    DB::raw('NULL as LineItemID')
                )
                ->first();

            if ($details) {
                return (array) $details;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get generic item details', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get items available from a procurement plan
     */
    private function getPlanAvailableItems($planId)
    {
        try {
            Log::info('Fetching plan items', [
                'plan_id' => $planId,
            ]);

            // Get RFQ Procurement Method ID
            $rfqMethodId = DB::table('t_CodeDetails')
                ->where('CodeID', 'ProcurementMethod')
                ->where('Value', 'R') // Assuming 'R' is for RFQ based on seeder
                ->value('ID');

            Log::info('RFQ Method ID lookup', ['id' => $rfqMethodId]);

            // Get all plan line items - filter availability in PHP
            $query = DB::table('t_PlanLineItem as pli')
                ->join('t_Items as itm', 'pli.ItemID', '=', 'itm.Id')
                ->leftJoin('t_ItemTypes as it', 'itm.ItemType', '=', 'it.Id')
                ->leftJoin('t_CodeDetails as cd', 'it.TypeName', '=', 'cd.ID')
                ->leftJoin('t_ItemCategories as cat', 'itm.Category', '=', 'cat.Id')
                ->where('pli.PlanID', $planId)
                ->where('pli.IsDeleted', 0)
                ->whereNull('itm.DeletedOn');

            // Apply RFQ filter if ID found
            if ($rfqMethodId) {
                $query->where('pli.ProcurementMethod', $rfqMethodId);
            } else {
                Log::warning('RFQ Procurement Method not found in CodeDetails');
            }

            $items = $query->select(
                'itm.Id',
                DB::raw("ISNULL(itm.ItemName, ISNULL(itm.ItemDescription, 'Unknown Item')) as Name"),
                'itm.ItemDescription as Description',
                'itm.ItemType as TypeId',
                'cd.Description as Type',
                'cat.Name as Category',
                'itm.Category as CategoryId',
                'pli.LineItemID as PlanLineRef',
                'pli.LineItemID',
                DB::raw('ISNULL(pli.MergedQty, ISNULL(pli.OriginalQTY, 0)) as PlanQuantity'),
                DB::raw('CASE WHEN pli.AdjustedCost > 0 THEN pli.AdjustedCost ELSE ISNULL(pli.EstimatedUnitCost, 0) END as UnitPrice'),
                'pli.UnitOfMeasure as UOM'
            )
                ->get();

            // Calculate usage and filter available items
            $availableItems = $items->map(function ($item) {
                // Calculate used quantity for this line item
                $usedQty = DB::table('t_RequisitionLines')
                    ->where('PlanLineRef', $item->LineItemID)
                    ->whereNull('DeletedOn')
                    ->sum('Quantity') ?? 0;

                $availableQty = $item->PlanQuantity - $usedQty;

                // Add calculated fields
                $item->UsedQuantity = $usedQty;
                $item->AvailableQuantity = $availableQty;

                return $item;
            })->filter(function ($item) {
                // Only return items with available quantity
                return $item->AvailableQuantity > 0;
            })->values(); // Reset array keys

            Log::info('Plan items query executed', [
                'plan_id' => $planId,
                'total_items' => $items->count(),
                'available_items' => $availableItems->count(),
            ]);

            return $availableItems;
        } catch (\Exception $e) {
            Log::error('Failed to fetch plan items', [
                'plan_id' => $planId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return collect([]);
        }
    }

    /**
     * Get all items of a specific type (not filtered by plan)
     */
    private function getGenericItemsByType($itemType)
    {
        try {
            Log::info('Fetching generic items by type', [
                'item_type' => $itemType,
                'item_type_type' => gettype($itemType),
            ]);

            // First, let's check what item types exist
            $existingTypes = DB::table('t_ItemTypes')->pluck('Id')->toArray();
            Log::info('Existing item type IDs', ['types' => $existingTypes]);

            // Check items for this type - direct query
            $directCount = DB::table('t_Items')
                ->where('ItemType', $itemType)
                ->whereNull('DeletedOn')
                ->count();
            Log::info('Direct items count for type', [
                'item_type' => $itemType,
                'direct_count' => $directCount,
            ]);

            // Check if the type exists
            $typeExists = DB::table('t_ItemTypes')->where('Id', $itemType)->exists();
            Log::info('Type exists check', [
                'item_type' => $itemType,
                'type_exists' => $typeExists,
            ]);

            // Try a simpler query first
            $simpleItems = DB::table('t_Items')
                ->where('ItemType', $itemType)
                ->whereNull('DeletedOn')
                ->select('Id', 'ItemName', 'ItemDescription', 'ItemType')
                ->get();
            Log::info('Simple items query result', [
                'item_type' => $itemType,
                'simple_count' => $simpleItems->count(),
                'sample' => $simpleItems->first(),
            ]);

            $items = DB::table('t_Items as itm')
                ->leftJoin('t_ItemTypes as it', 'itm.ItemType', '=', 'it.Id')
                ->leftJoin('t_CodeDetails as cd', 'it.TypeName', '=', 'cd.ID')
                ->leftJoin('t_UOM as uom', 'itm.UOM', '=', 'uom.Id')
                ->leftJoin('t_ItemCategories as cat', 'itm.Category', '=', 'cat.Id')
                ->where('itm.ItemType', $itemType)
                ->whereNull('itm.DeletedOn')
                ->select(
                    'itm.Id',
                    DB::raw('ISNULL(itm.ItemName, ISNULL(itm.ItemDescription, \'Unknown Item\')) as Name'),
                    'itm.ItemDescription as Description',
                    'itm.ItemType as TypeId',
                    'cd.Description as Type',
                    'cat.Name as Category',
                    'itm.Category as CategoryId',
                    DB::raw('0 as UnitPrice'),
                    DB::raw('ISNULL(uom.Code, \'Unit\') as UOM'),
                    DB::raw('NULL as PlanLineRef'),
                    DB::raw('NULL as AvailableQuantity')
                )
                ->orderBy('itm.ItemName')
                ->get();

            Log::info('Generic items by type fetched', [
                'item_type' => $itemType,
                'count' => $items->count(),
                'sample_item' => $items->first(),
            ]);

            return $items;
        } catch (\Exception $e) {
            Log::error('Failed to fetch generic items by type', [
                'item_type' => $itemType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return collect([]);
        }
    }

    /**
     * Get all items (not filtered by plan or type)
     */
    private function getGenericItems()
    {
        try {
            Log::info('Fetching generic items');

            $items = DB::table('t_Items as itm')
                ->leftJoin('t_ItemTypes as it', 'itm.ItemType', '=', 'it.Id')
                ->leftJoin('t_CodeDetails as cd', 'it.TypeName', '=', 'cd.ID')
                ->leftJoin('t_UOM as uom', 'itm.UOM', '=', 'uom.Id')
                ->leftJoin('t_ItemCategories as cat', 'itm.Category', '=', 'cat.Id')
                ->whereNull('itm.DeletedOn')
                ->select(
                    'itm.Id',
                    DB::raw('ISNULL(itm.ItemName, ISNULL(itm.ItemDescription, \'Unknown Item\')) as Name'),
                    'itm.ItemDescription as Description',
                    'itm.ItemType as TypeId',
                    'cd.Description as Type',
                    'cat.Name as Category',
                    'itm.Category as CategoryId',
                    DB::raw('0 as UnitPrice'),
                    DB::raw('ISNULL(uom.Code, \'Unit\') as UOM'),
                    DB::raw('NULL as PlanLineRef'),
                    DB::raw('NULL as AvailableQuantity')
                )
                ->orderBy('itm.ItemName')
                ->get();

            Log::info('Generic items fetched', [
                'count' => $items->count(),
            ]);

            return $items;
        } catch (\Exception $e) {
            Log::error('Failed to fetch generic items', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return collect([]);
        }
    }

    public function getRequisitionItems(): JsonResponse
    {
        try {
            $details = $this->service->getRequisitionItems();

            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inventory.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $this->authorize('viewAny', RequisitionLine::class);

        try {
            $details = $this->service->getRequisitionPriorityList();

            return view('procurement.requisitionItems.priorityList', compact('details'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
    }

    public function create($id)
    {
        $this->authorize('create', RequisitionLine::class);

        try {
            $details = $this->service->getRequisitionItems();

            // Check if requisition has a plan
            $requisition = DB::table('t_Requisitions')->where('Id', $id)->first();
            $hasPlan = ! empty($requisition->PlanRef);

            // Get item types for manual selection
            $types = DB::table('t_ItemTypes as it')
                ->leftJoin('t_CodeDetails as cd', 'it.TypeName', '=', 'cd.ID')
                ->whereNull('it.DeletedOn')
                ->select('it.Id', DB::raw('ISNULL(cd.Description, it.TypeName) as TypeName'))
                ->orderBy('TypeName')
                ->get();

            Log::info('Item types loaded for view', [
                'requisition_id' => $id,
                'has_plan' => $hasPlan,
                'types_count' => $types->count(),
                'types' => $types->toArray(),
            ]);

            return view('procurement.requisitionItems.create', compact('details', 'hasPlan', 'types'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
    }

    public function store(RequisitionItemRequest $request): JsonResponse
    {
        $this->authorize('create', RequisitionLine::class);

        try {
            $validatedData = $request->validated();

            $actor = $request->user();
            if (! $actor) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $requisitionAddLines = $this->service->addRequisitionLines(
                $validatedData['RequisitionID'],
                $validatedData['Item'],
                $validatedData['Quantity'],
                $validatedData['Urgency'],
                $validatedData['UOM'],
                $validatedData['EstimatedPrice'],
                $validatedData['LineItemID'] ?? null,
                $actor
            );

            if ($requisitionAddLines['status'] === 'success') {
                return response()->json([
                    'message' => $requisitionAddLines['message'],
                    'route' => route('requisition.show', $validatedData['RequisitionID']),
                ], 200);
            }

            Log::error('Failed to create requisitionLines', [
                'input' => $validatedData,
                'user_id' => $actor->id ?? null,
                'service_response' => $requisitionAddLines,
            ]);

            return response()->json([
                'message' => $requisitionAddLines['message'],
                'error' => $requisitionAddLines['error'] ?? 'Unknown error',
            ], 500);
        } catch (Throwable $e) {
            Log::error('Exception occurred while creating requisitionLines.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to create requisitionLines',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $this->authorize('view', Requisitions::query()->findOrFail($id));

        try {
            $details = $this->service->getRequisitionRelatedItems($id);

            return view('procurement.requisitionItems.create', compact('details'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
    }

    public function updateQuantity(Request $request, $lineId)
    {
        try {
            $result = RequisitionItemService::updateLineQuantity(
                $lineId,
                $request->input('quantity'),
                Auth::user()
            );

            if ($result['status'] === 'success') {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Controller: Failed to update quantity: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity',
            ], 500);
        }
    }

    public function destroy($lineId)
    {
        try {
            $result = RequisitionItemService::deleteRequisitionLine(
                $lineId,
                Auth::user()
            );

            if ($result['status'] === 'success') {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Controller: Failed to remove item: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item',
            ], 500);
        }
    }
}
