<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\PlanLineItems;

class PlanEditController extends Controller
{
    //
    public function index(Request $request)
{
    $planId = $request->input('PlanID');
    $draftItems = PlanLineItems::whereHas('consolidatedProcurementPlan', function ($query) {
        $query->where('Status', 'Draft');
    })->get();


    // dd($request->all());
    return view('procurement.procurementplan.planapproval.ammendplan.index', [
        'draftItems' => $draftItems,
        'PlanID' => $planId,
    ]);
}

public function updateDraftItems(Request $request)
{
    $itemIds = $request->input('lineItemIds', []);

    foreach ($itemIds as $id) {
        $qty = $request->input("qty_$id");
        $cost = $request->input("unitCost_$id");
        $remarks = $request->input("remarks_$id");

        PlanLineItems::where('LineItemID', $id)->update([
            'MergedQty' => $qty,
            'EstimatedUnitCost' => $cost,
            'ChangeRemarks' => $remarks,
            'ModifiedOn' => now(),
            'ModifiedBy' => auth()->id(),
        ]);
    }

    return redirect()->back()->with('success', 'Draft items updated successfully.');
}
public function deleteDraftItem($id)
    {
        PlanLineItems::where('LineItemID', $id)->update([
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        PlanLineItems::where('LineItemID', $id)->delete();

        return redirect()->back()->with('success', 'Item removed successfully.');
    }


    public function create(){
        return view('procurement.procurementplan.planapproval.ammendplan.create');
    }
}

