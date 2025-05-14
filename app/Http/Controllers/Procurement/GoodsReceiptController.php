<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\Requisitions;
use App\Models\Procurement\GoodsReceivedHeaderView;
use Illuminate\Support\Facades\Log;

class GoodsReceiptController extends Controller
{
    //
    public function index()
    {
        $goodsReceipts = GoodsReceivedHeaderView::all();
        return view('procurement.goodreceipts.index', compact('goodsReceipts'));
        //return view('procurement.goodreceipts.index');
    }

    public function create(){
        $Orders = Requisitions::select('Id', 'RequisitionNo', 'Remarks')->get();
        //$requisitionLines = RequisitionLines::all();
        $Orders = Requisitions::with('requisitionLines.category')->get();
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
        ]);
// dd($request->items);
        foreach ($request->items as $item) {

            GoodsReceipt::create([
                'GRNID'            => $request->GRNID,
                'ReceivedDate'     => now(),
                'ReceivedBy'       => $authUser->name,
                'POID'             => $request->POID,
                'SupplierId'       => Auth::id(),
                'ItemNo'           => $item['ItemNo'],
                'StoreID'          => 'STORE-001',
                'TransferTo'       => $item['TransferTo'],
                'TransferStatus'   => $item['TransferTo'],
                'POQTY'            => $item['POQTY'],
                'ReceivedQTY'      => $item['ReceivedQTY'],
                'TagRequired'      => isset($item['TagRequired']) ? 1 : 0,
                'InspectionStatus' => 'Pending',
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
