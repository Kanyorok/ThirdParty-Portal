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

        $Orders = DB::connection('sqlsrv')
            ->table('t_Orders')
            ->whereNotIn('OrderNo', $usedOrderNos)
            ->select('Id', 'OrderNo', 'ExtOrdNum', 'AccountID', 'OrdTotIncl')
            ->orderByDesc('CreatedOn')
            ->get();

        // Keep only fully approved POs using the centralized ApprovalService
        try {
            $approvalService = app(\App\Services\Core\ApprovalService::class);
            $Orders = $Orders->filter(function ($order) use ($approvalService) {
                $poId = (int)($order->Id ?? 0);
                $amount = (float)($order->OrdTotIncl ?? 0);
                return $poId > 0 && $approvalService->isFullyApproved('purchase_order', $poId, $amount);
            })->values();
        } catch (\Throwable $e) {
            // If approval check fails, fall back to showing none rather than unapproved POs
            Log::error('GRN create: approval filter failed', ['error' => $e->getMessage()]);
            $Orders = collect();
        }

        $OrderLines = DB::connection('sqlsrv')->table('t_OrderLines as ol')
            ->join('t_items as i', 'ol.iStockCodeID', '=', 'i.Id')
            ->leftJoin('t_CodeDetails as cd', 'i.InventoryType', '=', 'cd.Id')
            ->select(
                'ol.Id',
                'ol.iOrderID',
                'ol.iStockCodeID',
                'ol.fQuantity',
                'cd.Description as InventoryType',
                'i.ItemName',
                'i.ItemDescription',
                'i.Category',
                'i.UOM'
            )
            ->get();


        $linesGrouped = $OrderLines->groupBy('iOrderID');
        foreach ($Orders as $order) {
            $order->OrderLines = $linesGrouped[$order->Id] ?? collect();
        }

        return view('procurement.goodreceipts.create', compact('Orders'));
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

        DB::beginTransaction();
        try {
            foreach ($request->items as $index => $item) {
                $grn = GoodsReceipt::create([
                    'GRNID'            => $request->GRNID,
                    'ReceivedDate'     => now(),
                    'ReceivedBy'       => Auth::id(),
                    'POID'             => $request->POID,
                    'SupplierId'       => $request->SupplierID,
                    'ItemNo'           => $item['ItemNo'],
                    'StoreID'          => $item['StoreID'] ?? '2',
                    'TransferTo'       => $item['TransferTo'] ?? null,
                    'TransferStatus'   => $item['TransferTo'] ? 'Pending' : null,
                    'POQTY'            => $item['POQTY'] ?? 0,
                    'ReceivedQTY'      => $item['ReceivedQTY'] ?? 0,
                    'UnitPrice'        => $item['UnitPrice'] ?? 0,
                    'TagRequired'      => isset($item['TagRequired']) ? 1 : 0,
                    'InspectionStatus' => PostingEnum::Draft,
                    'CreatedBy'        => Auth::id(),
                    'ModifiedBy'       => Auth::id(),
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

                $stockItem = StockItem::where('ItemID', $grnLine->ItemNo)
                    ->where('Store', $grnLine->StoreID)
                    ->first();

                if ($stockItem) {
                    $stockItem->UnitCost = $grnLine->UnitPrice;
                    $stockItem->CurrentQty += (int) $grnLine->ReceivedQTY;
                    $stockItem->LastReceived = now();
                    $stockItem->ModifiedBy = Auth::id();
                    $stockItem->save();
                } else {
                    $stockItem = StockItem::create([
                        'SKUCode'      => 'SKU-' . $grnLine->ItemNo . '-' . $grnLine->StoreID . '-' . time(),
                        'ItemID'       => $grnLine->ItemNo,
                        'UOM'          => optional($grnLine->item)->UOM ?? 1,
                        'UnitCost'     => $grnLine->UnitPrice, 
                        'Store'        => $grnLine->StoreID,
                        'Branch'       => $grnLine->TransferTo ?? Auth::user()->BranchId,
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
                    'Store' => $grnLine->StoreID,
                    'Branch' => $grnLine->TransferTo ?? Auth::user()->BranchId,
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
}
