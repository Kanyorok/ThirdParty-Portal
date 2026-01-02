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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Throwable;

class RequisitionItemsController extends Controller
{
    public function __construct(protected RequisitionItemService $service, protected ItemService $itemService)
    {
        $this->middleware('ajax')->except(['index', 'create', 'show']);
    }

    /**
     * Get items by type - returns items from plan if available, otherwise all items
     */
    public function getItems(Request $request): JsonResponse
    {
        try {
            $requisitionId = $request->query('requisition_id');
            
            Log::info('getItems called', [
                'requisition_id' => $requisitionId
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
                    'plan_ref' => $planRef
                ]);
            }
            
            // If we have a plan, get plan-specific items with availability
            if ($planRef) {
                $items = $this->getPlanAvailableItems($planRef);
                
                Log::info('Plan items result', [
                    'plan_id' => $planRef,
                    'count' => $items->count()
                ]);
            }
            
            // Fallback to all items if no plan or no plan items available
            if ($items->isEmpty()) {
                Log::info('Fetching generic items (no plan items available)');
                
                $items = $this->getGenericItems();
            }
            
            Log::info('Final items to return', [
                'count' => $items->count(),
                'sample' => $items->first()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $items->values()->toArray(), // Ensure array keys are sequential
            ]);
            
        } catch (Exception $e) {
            Log::error('Failed to fetch items', [
                'requisition_id' => $request->query('requisition_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
                'plan_id' => $planId
            ]);

            // First try to get plan-specific details
            if ($planId) {
                $planDetails = $this->getPlanItemDetails($item, $planId);
                
                if ($planDetails) {
                    Log::info('Returning plan-specific item details', [
                        'item_id' => $item,
                        'details' => $planDetails
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'data' => [$planDetails], // Return as array for consistency
                    ]);
                }
            }

            // Fallback to generic item details
            $genericDetails = $this->getGenericItemDetails($item);
            
            Log::info('Returning generic item details', [
                'item_id' => $item,
                'details' => $genericDetails
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
                'trace' => $e->getTraceAsString()
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
            $details = DB::table('t_PlanLineItem as pli')
                ->join('t_Items as itm', 'pli.ItemID', '=', 'itm.Id')
                ->leftJoin('t_ItemCategories as cat', 'itm.Category', '=', 'cat.Id')
                ->where('pli.PlanID', $planId)
                ->where('pli.ItemID', $itemId)
                ->where('pli.IsDeleted', 0)
                ->whereNull('itm.DeletedOn')
                ->select(
                    'pli.UnitOfMeasure as UOM',
                    DB::raw('CASE WHEN pli.AdjustedCost > 0 THEN pli.AdjustedCost ELSE ISNULL(pli.EstimatedUnitCost, 0) END as UnitPrice'),
                    'itm.Category as CategoryId',
                    'cat.Name as CategoryName',
                    'pli.LineItemID'
                )
                ->first();

            if ($details) {
                return (array) $details;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to get plan item details', [
                'item_id' => $itemId,
                'plan_id' => $planId,
                'error' => $e->getMessage()
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
                    DB::raw('ISNULL(price.UnitPrice, 0) as UnitPrice'),
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
                'error' => $e->getMessage()
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
                'plan_id' => $planId
            ]);
            
            // Get all plan line items - filter availability in PHP
            $items = DB::table('t_PlanLineItem as pli')
                ->join('t_Items as itm', 'pli.ItemID', '=', 'itm.Id')
                ->leftJoin('t_ItemTypes as it', 'itm.ItemType', '=', 'it.Id')
                ->leftJoin('t_CodeDetails as cd', 'it.TypeName', '=', 'cd.ID')
                ->leftJoin('t_ItemCategories as cat', 'itm.Category', '=', 'cat.Id')
                ->where('pli.PlanID', $planId)
                ->where('pli.IsDeleted', 0)
                ->whereNull('itm.DeletedOn')
                ->select(
                    'itm.Id',
                    DB::raw('ISNULL(itm.ItemName, ISNULL(itm.ItemDescription, \'Unknown Item\')) as Name'),
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
            $availableItems = $items->map(function($item) {
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
            })->filter(function($item) {
                // Only return items with available quantity
                return $item->AvailableQuantity > 0;
            })->values(); // Reset array keys
            
            Log::info('Plan items query executed', [
                'plan_id' => $planId,
                'total_items' => $items->count(),
                'available_items' => $availableItems->count()
            ]);
            
            return $availableItems;
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch plan items', [
                'plan_id' => $planId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
                'count' => $items->count()
            ]);
            
            return $items;
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch generic items', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            return view('procurement.requisitionItems.create', compact('details'));
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
            if (!$actor) {
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
                    'route' => route('requisition.show', $validatedData['RequisitionID'])
                ], 200);
            }

            Log::error('Failed to create requisitionLines', [
                'input' => $validatedData,
                'user_id' => $actor->id ?? null,
                'service_response' => $requisitionAddLines,
            ]);

            return response()->json([
                'message' => $requisitionAddLines['message'],
                'error' => $requisitionAddLines['error'] ?? 'Unknown error'
            ], 500);
        } catch (Throwable $e) {
            Log::error('Exception occurred while creating requisitionLines.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create requisitionLines',
                'error' => $e->getMessage()
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
        //
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
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Controller: Failed to update quantity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity'
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
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Controller: Failed to remove item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item'
            ], 500);
        }
    }
}