<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\GoodsReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoodsReceiptController extends Controller
{

    public function index()
    {

    // Get only POs not used in GoodsReceipts  
        $goodsReceipts = GoodsReceipt::with('receiver', 'supplier')->where('InspectionStatus', 'd')->get();
        return view('procurement.goodreceipts.index', compact('goodsReceipts'));
    }

    public function create()
    {
        
        $usedOrderNos = DB::connection('sqlsrv')
        ->table('t_GoodsReceipts')
        ->distinct()
        ->pluck('POID');

        $Orders = DB::connection('sqlsrv')
            ->table('t_Orders')
            ->whereNotIn('OrderNo', $usedOrderNos)
            ->select('Id', 'OrderNo', 'ExtOrdNum', 'AccountID')
            ->get();

        // $Orders = DB::connection('sqlsrv')->table('t_Orders')
        //     ->select('Id', 'OrderNo', 'Description','AccountID')
        //     ->get();

       
        $OrderLines = DB::connection('sqlsrv')->table('t_OrderLines as ol')
            ->join('t_items as i', 'ol.iStockCodeID', '=', 'i.Id')
            ->select(
                'ol.Id',
                'ol.iOrderID',
                'ol.iStockCodeID',
                'ol.fQuantity',
                'i.InventoryType',
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

        $authUser = Auth::user();
        Log::info('Store method reached');
        Log::info('Request data:', $request->all());
        $request->validate([
            'GRNID' => 'required|string',
            'POID' => 'required|string',
            'items' => 'required|array',
            'SupplierID' => 'required',
        ]);

        foreach ($request->items as $item) {

            GoodsReceipt::create([
                'GRNID'            => $request->GRNID,
                'ReceivedDate'     => now(),
                'ReceivedBy'       => Auth::id(),
                'POID'             => $request->POID,
                'SupplierId'       => $request->SupplierID,
                'ItemNo'           => $item['ItemNo'],
                'StoreID'          => 'STORE-001',
                'TransferTo'       => $item['TransferTo'],
                'TransferStatus'   => $item['TransferTo'],
                'POQTY'            => $item['POQTY'],
                'ReceivedQTY'      => $item['ReceivedQTY'],
                'TagRequired'      => isset($item['TagRequired']) ? 1 : 0,
                'InspectionStatus' => PostingEnum::Draft,
                'CreatedBy'        => Auth::id(),
                'ModifiedBy'       => Auth::id(),
            ]);

        }
        return redirect()->route('procurementreceipts.index')->with('success',  'Goods receipt saved successfully.');
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
        $grnId = $request->input('grn_id');
        $poId = $request->input('po_id');

        $grnLines = GoodsReceipt::where('GRNID', $grnId)
                        ->where('POID', $poId)
                        ->get();

        if ($grnLines->isEmpty()) {
            return response()->json(['error' => 'GRN lines not found.'], 404);
        }

        // Update TransferStatus to "Posted"
        foreach ($grnLines as $line) {
            $line->InspectionStatus = PostingEnum::Posted;
            $line->save();

            // Insert into t_StockTransactions
            // DB::table('t_StockTransactions')->insert([
            //     'TransactionDate' => now(),
            //     'GRNID' => $grnId,
            //     'POID' => $poId,
            //     'ItemNo' => $line->ItemNo,
            //     'StoreID' => $line->StoreID,
            //     'Quantity' => $line->ReceivedQTY,
            //     'TransactionType' => 'Receipt',
            //     'CreatedBy' => auth()->id(),
            //     'CreatedOn' => now(),
            // ]);
        }

        return response()->json(['message' => 'Receipt posted and transactions recorded.']);
    }



}
