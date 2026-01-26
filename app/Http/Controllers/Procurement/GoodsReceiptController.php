<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Inventory\StockGRNLedger;
use App\Models\Inventory\StockItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\Branch;

class GoodsReceiptController extends Controller
{

    public function index()
    {
        $this->authorize('viewAny', GoodsReceipt::class);

        $goodsReceipts = GoodsReceipt::with('receiver', 'supplier')
            ->where('InspectionStatus', PostingEnum::Draft)
            ->selectRaw('MIN(id) as id') 
            ->groupBy('GRNID')
            ->get()
            ->map(function ($receipt) {
                return GoodsReceipt::with('receiver', 'supplier')->find($receipt->id);
            });


        return view('procurement.goodreceipts.index', compact('goodsReceipts'));
    }

    public function create()
    {
        $this->authorize('create', GoodsReceipt::class);
        $usedOrderNos = DB::connection('sqlsrv')
            ->table('t_GoodsReceipts')
            ->distinct()
            ->pluck('POID');

        // Fetch approved POs not yet used in GRNs
        $Orders = DB::connection('sqlsrv')
            ->table('t_Orders')
            ->whereNotIn('OrderNo', $usedOrderNos)
            ->where(function($query) {
                $query->where('DocStatus', 'A')  // Approved
                      ->orWhere('DocStatus', 'a'); // Handle case variations
            })
            ->select('Id', 'OrderNo', 'ExtOrdNum', 'AccountID', 'OrdTotIncl')
            ->orderByDesc('CreatedOn')
            ->get();

        $OrderLines = DB::connection('sqlsrv')->table('t_OrderLines as ol')
            ->join('t_items as i', 'ol.iStockCodeID', '=', 'i.Id')
            ->leftJoin('t_CodeDetails as cd', 'i.InventoryType', '=', 'cd.Id')
            ->select(
                'ol.Id',
                'ol.iOrderID',
                'ol.iStockCodeID',
                'ol.fQuantity',
                'ol.fUnitPriceExcl',
                'cd.Description as InventoryType',
                'i.ItemName',
                'i.ItemDescription',
                'i.Category',
                'i.UOM'
            )
            ->get();


        $linesGrouped = $OrderLines->groupBy('iOrderID');
        
        Log::info('GRN Create Debug', [
            'total_orders' => $Orders->count(),
            'total_lines' => $OrderLines->count(),
            'grouped_keys' => $linesGrouped->keys()->toArray()
        ]);
        
        foreach ($Orders as $order) {
            $order->OrderLines = $linesGrouped[$order->Id] ?? collect();
            Log::info('Order Lines Attached', [
                'order_id' => $order->Id,
                'order_no' => $order->OrderNo,
                'lines_count' => $order->OrderLines->count()
            ]);
        }
        
        // Fetch all active stores
        $stores = DB::connection('sqlsrv')->table('t_Stores')->select('Id', 'StoreName')->get();

        return view('procurement.goodreceipts.create', compact('Orders', 'stores'));
    }


       public function store(Request $request)
    {
        $this->authorize('create', GoodsReceipt::class);
        
        $request->validate([
            'GRNID' => 'required|string',
            'POID' => 'required|string',
            'items' => 'required|array',
            'SupplierID' => 'required',
        ]);

        // Get a valid default store
        $defaultStoreId = DB::connection('sqlsrv')->table('t_Stores')->where('Id', 1)->exists() ? 1 : DB::connection('sqlsrv')->table('t_Stores')->value('Id');
        
        // If no stores exist, return error - cannot create GRN without a valid store
        if (!$defaultStoreId) {
            return back()->withErrors([
                'error' => 'Cannot create GRN: No stores are configured in the system. Please create at least one store before creating goods receipts.'
            ])->withInput();
        }

        // Get HQ Branch
        $hqBranch = Branch::where('IsHQ', 1)->first();

        DB::beginTransaction();
        try {
            foreach ($request->items as $index => $item) {
                $branchId = $item['TransferTo'] ?? Auth::user()->BranchId;
                
                $mainStore = $this->getDefaultStoreForBranch($branchId);
                
                if (!$mainStore) {
                    throw new \Exception("No main store found for branch {$branchId}");
                }
                
                $grn = GoodsReceipt::create([
                    'GRNID'            => $request->GRNID,
                    'ReceivedDate'     => now(),
                    'ReceivedBy'       => Auth::id(),
                    'POID'             => $request->POID,
                    'SupplierId'       => $request->SupplierID,
                    'ItemNo'           => $item['ItemNo'],
                    'StoreID'          => $item['StoreID'] ?? $defaultStoreId,
                    'TransferTo'       => $hqBranch ? $hqBranch->Id : null,
                    'TransferStatus'   => $item['TransferTo'] ?? null,
                    'POQTY'            => $item['POQTY'] ?? 0,
                    'ReceivedQTY'      => $item['ReceivedQTY'] ?? 0,
                    'UnitPrice'        => $item['UnitPrice'] ?? 0,
                    'TagRequired'      => isset($item['TagRequired']) ? 1 : 0,
                    'InspectionStatus' => PostingEnum::Draft,
                    'CreatedBy'        => Auth::id(),
                    'ModifiedBy'       => Auth::id(),
                ]);

                $existingStock = StockItem::where('ItemID', $grn->ItemNo)
                    ->where('Store', $grn->StoreID)
                    ->first();

                if ($existingStock) {
                    $existingStock->CurrentQty += (int) $grn->ReceivedQTY;
                    $existingStock->LastReceived = now();
                    $existingStock->ModifiedBy = Auth::id();
                    $existingStock->save();

                    Log::info("Stock updated for ItemID {$grn->ItemNo}, new qty: {$existingStock->CurrentQty}");
                } else {
                    StockItem::create([
                        'SKUCode'      => 'SKU-' . $grn->ItemNo . '-' . time(),
                        'ItemID'       => $grn->ItemNo,
                        'UOM'          => optional($grn->item)->UOM,
                        'UnitCost'     => optional($grn->item)->UnitCost ?? 0,
                        'Store'        => $grn->StoreID,
                        'Branch'       => $authUser->BranchId ?? null,
                        'CurrentQty'   => (int) $grn->ReceivedQTY,
                        'Min'          => 0,
                        'Reorder'      => 0,
                        'Max'          => 0,
                        'LastReceived' => now(),
                        'Status'       => true,
                        'CreatedBy'    => Auth::id(),
                        'CreatedOn'    => now(),
                        'ModifiedBy'   => Auth::id(),
                        'ModifiedOn'   => now(),
                    ]);

                    Log::info("New StockItem created for ItemID {$grn->ItemNo}");
                }
            }

            DB::commit();

            return redirect()
                ->route('procurementreceipts.index')
                ->with('success', 'Goods Receipt created successfully in draft status.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Goods Receipt: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to save GRN: ' . $e->getMessage()]);
        }
    }

    public function fetchLinesByGRN($grnId, $poId)
    {
        $items = GoodsReceipt::where('GRNID', $grnId)
            ->where('POID', $poId)
            ->get();

        return response()->json($items);
    }

       public function updateLine(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:t_GoodsReceipts,id',
            'items.*.ReceivedQTY' => 'required|numeric|min:0',
            'items.*.TransferTo' => 'nullable|string',
            'items.*.TagRequired' => 'required|boolean',
        ]);

        foreach ($request->items as $item) {
            $line = GoodsReceipt::find($item['id']);
            
            if (isset($item['TransferTo']) && $item['TransferTo'] != $line->TransferTo) {
                $branchId = $item['TransferTo'] ?? Auth::user()->BranchId;
                $mainStore = $this->getDefaultStoreForBranch($branchId);
                
                if ($mainStore) {
                    $line->StoreID = $mainStore->Id;
                }
            }
            
            $line->ReceivedQTY = $item['ReceivedQTY'];
            $line->TransferTo = $item['TransferTo'];
            $line->TagRequired = $item['TagRequired'];
            $line->ModifiedOn = now();
            $line->save();
        }

        return redirect()->route('procurementreceipts.index')->with('success', 'All GRN line items updated.');
    }
    public function destroy($grnId, $poId)
    {
        $deleted = GoodsReceipt::where('GRNID', $grnId)
            ->where('POID', $poId)
            ->update([
                'DeletedBy' => Auth::id(),
                'DeletedOn' => now(),
            ]);

        if ($deleted) {
            return redirect()->route('procurementreceipts.index')->with('success', 'GRN items soft-deleted successfully.');
        } else {
            return redirect()->route('procurementreceipts.index')->with('error', 'No records found to delete.');
        }
    }


   public function postReceipt(Request $request)
{
    $this->authorize('post', GoodsReceipt::class);
    
    $grnId = $request->input('grn_id');
    $poId = $request->input('po_id');

    $grnLines = GoodsReceipt::with('item')
        ->where('GRNID', $grnId)
        ->where('POID', $poId)
        ->where('InspectionStatus', PostingEnum::Draft)
        ->get();

    if ($grnLines->isEmpty()) {
        return response()->json(['error' => 'No draft GRN lines found.'], 404);
    }

    DB::beginTransaction();
    try {
        foreach ($grnLines as $grnLine) {
            $grnLine->InspectionStatus = PostingEnum::Posted;
            $grnLine->save();

            // Get the branch for this GRN line
            $branchId = $grnLine->TransferTo ?? Auth::user()->BranchId;
            
            // Get the main store for this branch
            $mainStore = $this->getDefaultStoreForBranch($branchId);
            
            if (!$mainStore) {
                throw new \Exception("No main store found for branch {$branchId}");
            }

            $stockItem = StockItem::where('ItemID', $grnLine->ItemNo)
                ->where('Store', $mainStore->Id) // Use the main store ID
                ->where('Branch', $branchId) // Also check the branch
                ->first();

            if ($stockItem) {
                $stockItem->UnitCost = $grnLine->UnitPrice;
                $stockItem->CurrentQty += (int) $grnLine->ReceivedQTY;
                $stockItem->LastReceived = now();
                $stockItem->ModifiedBy = Auth::id();
                $stockItem->save();
            } else {
                $stockItem = StockItem::create([
                    'SKUCode'      => 'SKU-' . $grnLine->ItemNo . '-' . $mainStore->Id . '-' . time(),
                    'ItemID'       => $grnLine->ItemNo,
                    'UOM'          => optional($grnLine->item)->UOM ?? 1,
                    'UnitCost'     => $grnLine->UnitPrice, 
                    'Store'        => $mainStore->Id, // Use the main store ID
                    'Branch'       => $branchId,
                    'CurrentQty'   => (int) $grnLine->ReceivedQTY,
                    'Min'          => 0,
                    'Reorder'      => 0,
                    'Max'          => 0,
                    'LastReceived' => now(),
                    'Status'       => true,
                    'CreatedBy'    => Auth::id(),
                    'CreatedOn'    => now(),
                    'ModifiedBy'   => Auth::id(),
                    'ModifiedOn'   => now(),
                ]);
            }

            StockGRNLedger::create([
                'GRNID' => $grnLine->GRNID,
                'GoodsReceiptId' => $grnLine->id,
                'StockItemId' => $stockItem->Id,
                'ItemNo' => $grnLine->ItemNo,
                'SKUCode' => $stockItem->SKUCode,
                'ReceivedQTY' => $grnLine->ReceivedQTY,
                'RemainingQTY' => $grnLine->ReceivedQTY,
                'UnitPrice' => $grnLine->UnitPrice,
                'Store' => $mainStore->Id, // Use the main store ID for the ledger
                'Branch' => $branchId,
                'ReceivedDate' => now(),
                'CreatedBy'    => Auth::id(),
                'CreatedOn'    => now(),
                'ModifiedBy'   => Auth::id(), 
                'ModifiedOn'   => now(),
            ]);
        }

        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'GRN posted successfully. Stock updated and ledger created.',
            'grn_id' => $grnId
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error posting GRN: ' . $e->getMessage());
        return response()->json([
            'error' => true,
            'message' => 'Failed to post GRN: ' . $e->getMessage()
        ], 500);
    }
}

protected function getDefaultStoreForBranch($branchId)
{
    $store = Store::where('BranchID', $branchId)
        ->where('Status', true)
        ->where('IsMainStore', true)
        ->first();

    return $store;
}

     
}
