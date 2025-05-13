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
                'ReceivedBy'       => Auth::id(),
                'POID'             => $request->POID,
                'ItemNo'           => $item['ItemNo'],
                'StoreID'          => $item['TransferTo'],
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


}
