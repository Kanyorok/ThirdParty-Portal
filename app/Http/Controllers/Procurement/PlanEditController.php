<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;
use Illuminate\Http\Request;

class PlanEditController extends Controller
{
    //
    public function index(Request $request)
{
    $planId = $request->input('PlanID');
    $plan = ConsolidatedProcurementPlan::where('PlanID', $planId)->where('Status', 'd')->with('lineItems')->get();
    if (!$plan instanceof ConsolidatedProcurementPlan) {
        return redirect()->back()->with('error', 'Plan not found.');
    }



    return view('procurement.procurementplan.planapproval.ammendplan.index', [
        'draftItems' => $plan->lineItems,
        'PlanID' => $planId,
    ]);
}

    public function updateDraftItems(Request $request, ConsolidatedProcurementPlan $plan)
{
    $actor = $request->user();
    $itemIds = $request->input('lineItemIds', []);
//todo validate
    foreach ($itemIds as $id) {
        $qty = $request->input("qty_$id");
        $cost = $request->input("unitCost_$id");
        $remarks = $request->input("remarks_$id");

        PlanLineItems::where('LineItemID', $id)->update([
            'MergedQty' => $qty,
            'EstimatedUnitCost' => $cost,
            'ChangeRemarks' => $remarks,
            'ModifiedOn' => now(),
            'ModifiedBy' => $actor->id
        ]);
        activity()->causedBy($actor)->performedOn('plan line')->event('update')->log("Update plan line item $id.");
    }


        return redirect()->back()->with('success', 'Draft items updated successfully.');
}

    public function deleteDraftItem($id, ConsolidatedProcurementPlan $plan)
    {
        //todo validate
        PlanLineItems::where('LineItemID', $id)->update([
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        PlanLineItems::where('LineItemID', $id)->delete();

        activity()->causedBy($actor)->performedOn('plan line')->event('create')->log("Deleted $id.");

        return redirect()->back()->with('success', 'Item removed successfully.');
    }


    public function create(){
        return view('procurement.procurementplan.planapproval.ammendplan.create');
    }
}

