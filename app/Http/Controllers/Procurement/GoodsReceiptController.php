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
    //
    public function index()
    {
        $goodsReceipts = GoodsReceipt::with('receiver','supplier')->get();
        return view('procurement.goodreceipts.index', compact('goodsReceipts'));
        //return view('procurement.goodreceipts.index');
    }

    public function create()
    {
        // Fetch all orders
        $Orders = DB::connection('sqlsrv')->table('t_Orders')
            ->select('Id', 'OrderNo', 'Description','AccountID')
            ->get();

        // Fetch order lines with item details using a JOIN
        $OrderLines = DB::connection('sqlsrv')->table('t_OrderLines as ol')
            ->join('t_items as i', 'ol.iStockCodeID', '=', 'i.Id')
            ->select(
                'ol.Id',
                'ol.iOrderID',
                'ol.iStockCodeID',
                'ol.fQuantity',
                'i.InventoryType', // assuming you have this field
                'i.ItemName',
                'i.ItemDescription',
                'i.Category',
                'i.UOM'
            )
            ->get();

        // Group lines by Order ID
        $linesGrouped = $OrderLines->groupBy('iOrderID');

        // Attach lines to each order
        foreach ($Orders as $order) {
            $order->OrderLines = $linesGrouped[$order->Id] ?? collect();
        }

        return view('procurement.goodreceipts.create', compact('Orders'));
    }


    public function store(Request $request)
    {
    //dd($request->all());
        $authUser = Auth::user();
        Log::info('Store method reached');
        Log::info('Request data:', $request->all());
        $request->validate([
            'GRNID' => 'required|string',
            'POID' => 'required|string',
            'items' => 'required|array',
            'SupplierID' => 'required',
        ]);
// dd($request->items);
        foreach ($request->items as $item) {

            GoodsReceipt::create([
                'GRNID'            => $request->GRNID,
                'ReceivedDate'     => now(),
                'ReceivedBy'       => $authUser->name,
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
        return redirect()->back()->with('success', 'Goods receipt saved successfully.');
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



}
