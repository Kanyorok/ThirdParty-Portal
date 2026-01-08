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

    public function getItems(Request $request, $type = null): JsonResponse
    {
        try {
            $requisitionId = $request->query('requisition_id');
            $planRef = null;
            $items = collect([]);

            if ($requisitionId) {
                $requisition = DB::table('t_Requisitions')
                    ->where('Id', $requisitionId)
                    ->first();

                $planRef = $requisition->PlanRef ?? null;
            }

            if ($planRef) {
                $items = $this->getPlanAvailableItems($planRef);
            } else {
                if ($type && $type !== 'all') {
                    $items = $this->getGenericItemsByType($type);
                } else {
                    $items = $this->getGenericItems();
                }
            }

            return response()->json([
                'success' => true,
                'data' => $items->values()->toArray(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch items', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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
            if ($planId) {
                $planDetails = $this->getPlanItemDetails($item, $planId);

                if ($planDetails) {
                    return response()->json([
                        'success' => true,
                        'data' => [$planDetails],
                    ]);
                }
            }

            $genericDetails = $this->getGenericItemDetails($item);

            return response()->json([
                'success' => true,
                'data' => $genericDetails ? [$genericDetails] : [],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }

    private function getPlanItemDetails($itemId, $planId)
    {
        try {
            $planItems = DB::table('t_PlanLineItem as pli')
                ->join('t_Items as itm', 'pli.ItemID', '=', 'itm.Id')
                ->leftJoin('t_ItemCategories as cat', 'itm.Category', '=', 'cat.Id')
                ->leftJoin('t_UOM as uom', 'itm.UOM', '=', 'uom.Id')
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
            return null;
        }
    }

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

            return $details ? (array) $details : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getPlanAvailableItems($planId)
    {
        try {
            $rfqMethodId = DB::table('t_CodeDetails')
                ->where('CodeID', 'ProcurementMethod')
                ->where('Value', 'R')
                ->value('ID');

            $query = DB::table('t_PlanLineItem as pli')
                ->join('t_Items as itm', 'pli.ItemID', '=', 'itm.Id')
                ->leftJoin('t_ItemTypes as it', 'itm.ItemType', '=', 'it.Id')
                ->leftJoin('t_CodeDetails as cd', 'it.TypeName', '=', 'cd.ID')
                ->leftJoin('t_ItemCategories as cat', 'itm.Category', '=', 'cat.Id')
                ->where('pli.PlanID', $planId)
                ->where('pli.IsDeleted', 0)
                ->whereNull('itm.DeletedOn');

            if ($rfqMethodId) {
                $query->where('pli.ProcurementMethod', $rfqMethodId);
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

            return $items->map(function ($item) {
                $usedQty = DB::table('t_RequisitionLines')
                    ->where('PlanLineRef', $item->LineItemID)
                    ->whereNull('DeletedOn')
                    ->sum('Quantity') ?? 0;

                $item->UsedQuantity = $usedQty;
                $item->AvailableQuantity = $item->PlanQuantity - $usedQty;
                return $item;
            })->filter(function ($item) {
                return $item->AvailableQuantity > 0;
            })->values();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    private function getGenericItemsByType($itemType)
    {
        try {
            return DB::table('t_Items as itm')
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
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    private function getGenericItems()
    {
        try {
            return DB::table('t_Items as itm')
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
        } catch (\Exception $e) {
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
            $requisition = DB::table('t_Requisitions')->where('Id', $id)->first();
            $hasPlan = !empty($requisition->PlanRef);
            $types = DB::table('t_ItemTypes as it')
                ->leftJoin('t_CodeDetails as cd', 'it.TypeName', '=', 'cd.ID')
                ->whereNull('it.DeletedOn')
                ->select('it.Id', DB::raw('ISNULL(cd.Description, it.TypeName) as TypeName'))
                ->orderBy('TypeName')
                ->get();

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

            return response()->json([
                'message' => $requisitionAddLines['message'],
                'error' => $requisitionAddLines['error'] ?? 'Unknown error'
            ], 500);
        } catch (Throwable $e) {
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

    public function updateQuantity(Request $request, $lineId)
    {
        try {
            $result = RequisitionItemService::updateLineQuantity(
                $lineId,
                $request->input('quantity'),
                Auth::user()
            );

            return response()->json([
                'success' => $result['status'] === 'success',
                'message' => $result['message']
            ], $result['status'] === 'success' ? 200 : 400);
        } catch (\Exception $e) {
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

            return response()->json([
                'success' => $result['status'] === 'success',
                'message' => $result['message']
            ], $result['status'] === 'success' ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item'
            ], 500);
        }
    }
}
