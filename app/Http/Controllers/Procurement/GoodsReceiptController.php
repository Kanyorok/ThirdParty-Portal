<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\GoodsReceipt;

class GoodsReceiptController extends Controller
{
    //
    public function index()
    {
        $goodsReceipts = GoodsReceipt::all();
        return view('procurement.goodreceipts.index', compact('goodsReceipts'));
        //return view('procurement.goodreceipts.index');
    }

    public function create(){
        return view('procurement.goodreceipts.create');
    }

    //Saving the Good Receipts on creation
    public function store(Request $request)
    {
        $validated = $request->validate([
            'GRNID'  =>  'nullable|string|max:55',
            'POID'   =>  'nullable|string|max:55',
            'ReceivedDate'  =>  'required|date',
            'StoreID'    =>  'nullable|string|max:55',
            'ReceivedBy'     =>  'nullable|string|max:55',
            'InspectionStatus'   =>  'nullable|string|max:55',
            'TransferStatus'     =>  'nullable|string|max:55',
            'ItemNo'    =>  'nullable|string|max:55',
            'POQTY' => 'nullable|decimal|max:55',
            'ReceivedQTY'   => 'nullable|decimal|max:55',
            'TransferTo'  =>  'nullable|string|max:55',
            'CreatedBy'  =>  'nullable|string|max:55',
            'ModifiedBy'     =>  'nullable|string|max:55',
            'SasraAuditorId' => 'required|exists:t_SasraAuditors,Id',
            'EngagementStartDate' => 'required|date',
            'EngagementEndDate' => 'nullable|date|after_or_equal:EngagementStartDate',
            'EngagementStatus' => 'nullable|string|max:55',
        ]);
        $validated['CreatedBy'] = Auth::id();
        $validated['ModifiedBy'] = Auth::id(); 
    }
}
