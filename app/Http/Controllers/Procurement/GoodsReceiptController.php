<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\Inventory\StockGRNLedger;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\Store;
use App\Models\Procurement\GoodsReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Get total received quantities per PO line item from existing GRNs
        $receivedQuantities = DB::connection('sqlsrv')
            ->table('t_GoodsReceipts')
            ->select('POID', 'ItemNo', DB::raw('SUM(ReceivedQTY) as TotalReceived'))
            ->whereNull('DeletedOn')
            ->groupBy('POID', 'ItemNo')
            ->get()
            ->groupBy(fn ($item) => (int) $item->POID) // Cast POID to int for consistent matching
            ->map(function ($items) {
                return $items->keyBy(fn ($item) => (int) $item->ItemNo)->map(fn ($item) => (float) $item->TotalReceived);
            });

        // Fetch all approved POs with their BranchID
        $Orders = DB::connection('sqlsrv')
            ->table('t_Orders')
            ->leftJoin('t_Branches', 't_Orders.BranchID', '=', 't_Branches.Id')
            ->where(function ($query) {
                $query->where('t_Orders.DocStatus', 'A')  // Approved
                      ->orWhere('t_Orders.DocStatus', 'a'); // Handle case variations
            })
            ->select('t_Orders.Id', 't_Orders.OrderNo', 't_Orders.ExtOrdNum', 't_Orders.AccountID', 't_Orders.OrdTotIncl', 't_Orders.BranchID', 't_Branches.Name as BranchName')
            ->orderByDesc('t_Orders.CreatedOn')
            ->get();

        // Fetch all order lines with item details
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

        // Filter orders to only include those with remaining items
        $filteredOrders = collect();

        foreach ($Orders as $order) {
            $orderLines = $linesGrouped[$order->Id] ?? collect();
            $receivedForPO = $receivedQuantities[(int) $order->Id] ?? collect();

            // Calculate remaining quantities for each line
            $linesWithRemaining = $orderLines->map(function ($line) use ($receivedForPO) {
                $received = $receivedForPO[(int) $line->iStockCodeID] ?? 0;
                $remaining = $line->fQuantity - $received;

                // Add remaining quantity to line object
                $line->fReceivedSoFar = $received;
                $line->fRemainingQty = max(0, $remaining);

                return $line;
            })->filter(function ($line) {
                // Only keep lines with remaining quantity > 0
                return $line->fRemainingQty > 0;
            });

            // Only include PO if it has remaining items
            if ($linesWithRemaining->isNotEmpty()) {
                $order->OrderLines = $linesWithRemaining->values();
                $filteredOrders->push($order);
            }
        }

        $Orders = $filteredOrders;

        Log::info('GRN Create Debug', [
            'total_orders' => $Orders->count(),
            'total_lines' => $OrderLines->count(),
            'received_po_ids' => $receivedQuantities->keys()->toArray(),
            'grouped_keys' => $linesGrouped->keys()->toArray(),
        ]);

        // Fetch all active stores
        $stores = DB::connection('sqlsrv')->table('t_Stores')->select('Id', 'StoreName')->get();

        // Fetch all branches for TransferTo dropdown
        $branches = Branch::select('Id', 'Name')->where('IsHq', 1)->get();

        return view('procurement.goodreceipts.create', compact('Orders', 'stores', 'branches'));
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
        if (! $defaultStoreId) {
            return back()->withErrors([
                'error' => 'Cannot create GRN: No stores are configured in the system. Please create at least one store before creating goods receipts.',
            ])->withInput();
        }

        // Get HQ Branch
        $hqBranch = Branch::where('IsHQ', 1)->first();

        DB::beginTransaction();

        try {
            foreach ($request->items as $index => $item) {
                // TransferTo may contain string values like "Checkin" - only use if numeric
                $transferTo = $item['TransferTo'] ?? null;
                $branchId = is_numeric($transferTo) ? (int) $transferTo : Auth::user()->BranchId;

                $mainStore = $this->getDefaultStoreForBranch($branchId);

                if (! $mainStore) {
                    throw new \Exception("No main store found for branch {$branchId}");
                }

                $grn = GoodsReceipt::create([
                    'GRNID' => $request->GRNID,
                    'ReceivedDate' => now(),
                    'ReceivedBy' => Auth::id(),
                    'POID' => $request->POID,
                    'SupplierId' => $request->SupplierID,
                    'ItemNo' => $item['ItemNo'],
                    'StoreID' => $item['StoreID'] ?? $defaultStoreId,
                    'TransferTo' => $hqBranch ? $hqBranch->Id : null,
                    'TransferStatus' => $item['TransferTo'] ?? null,
                    'POQTY' => $item['POQTY'] ?? 0,
                    'ReceivedQTY' => $item['ReceivedQTY'] ?? 0,
                    'UnitPrice' => $item['UnitPrice'] ?? 0,
                    'TagRequired' => isset($item['TagRequired']) ? 1 : 0,
                    'InspectionStatus' => PostingEnum::Draft,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);

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

        $request->validate([
            'grn_id' => 'required|string',
            'po_id' => 'required|string',
        ]);

        $grnId = $request->input('grn_id');
        $poId = $request->input('po_id');

        $grnLines = GoodsReceipt::with('item')
            ->where('GRNID', $grnId)
            ->where('POID', $poId)
            ->where('InspectionStatus', PostingEnum::Draft)
            ->get();

        if ($grnLines->isEmpty()) {
            return response()->json([
                'error' => true,
                'message' => 'No draft items found for this GRN.',
            ], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($grnLines as $grnLine) {
                // Determine Branch and Store for this line
                // Default to user's branch if TransferTo is null/invalid
                $branchId = (is_numeric($grnLine->TransferTo) ? (int)$grnLine->TransferTo : Auth::user()->BranchId);

                // Use the store defined on the line, or fetch main store for the branch
                if (! empty($grnLine->StoreID)) {
                    $storeId = $grnLine->StoreID;
                } else {
                    $mainStore = $this->getDefaultStoreForBranch($branchId);
                    if (! $mainStore) {
                        throw new \Exception("No main store found for branch ID: {$branchId}");
                    }
                    $storeId = $mainStore->Id;
                }

                $stockItem = StockItem::where('ItemID', $grnLine->ItemNo)
                    ->where('Store', $storeId)
                    ->where('Branch', $branchId)
                    ->first();

                if ($stockItem) {
                    $stockItem->UnitCost = $grnLine->UnitPrice ?? 0;
                    $stockItem->CurrentQty += (int) $grnLine->ReceivedQTY;
                    $stockItem->LastReceived = now();
                    $stockItem->ModifiedBy = Auth::id();
                    $stockItem->save();
                } else {
                    $stockItem = StockItem::create([
                        'SKUCode' => 'SKU-' . $grnLine->ItemNo . '-' . $storeId . '-' . time(),
                        'ItemID' => $grnLine->ItemNo,
                        'UOM' => optional($grnLine->item)->UOM ?? 1,
                        'UnitCost' => $grnLine->UnitPrice ?? 0,
                        'Store' => $storeId,
                        'Branch' => $branchId,
                        'CurrentQty' => (int) $grnLine->ReceivedQTY,
                        'Min' => 0,
                        'Reorder' => 0,
                        'Max' => 0,
                        'LastReceived' => now(),
                        'Status' => true,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
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
                    'UnitPrice' => $grnLine->UnitPrice ?? 0,
                    'Store' => $storeId,
                    'Branch' => $branchId,
                    'ReceivedDate' => now(),
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Update line status to Posted
                $grnLine->InspectionStatus = PostingEnum::Posted;
                $grnLine->save();
            }

            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'GRN Posted Successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error posting GRN: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Failed to post GRN: ' . $e->getMessage(),
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
