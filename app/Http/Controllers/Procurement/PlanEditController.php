<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\PlanLineItems;
use App\Enums\ProcurementPlanStatusEnum;
use App\Models\Procurement\ConsolidatedProcurementPlan;

class PlanEditController extends Controller
{
    //
    public function index(Request $request)
    {
        $planId = $request->input('PlanID');

    $availablePlans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Draft)->get();

    $draftItems = collect();
    if ($planId) {
        $draftItems = PlanLineItems::where('PlanID', $planId)
            ->whereHas('consolidatedProcurementPlan', function ($query) {
                $query->where('Status', ProcurementPlanStatusEnum::Draft);
            })
            ->get();
    }

        return view('procurement.procurementplan.planapproval.ammendplan.index', [
            'draftItems' => $draftItems,
            'PlanID' => $planId,
            'availablePlans' => $availablePlans,
        ]);
    }

    public function updateDraftItems(Request $request)
    {
        
        $user = auth()->user();
        $itemIds = $request->input('lineItemIds', []);
       // $this->authorize('update', $itemIds);

        foreach ($itemIds as $id) {
            $qty = $request->input("qty_$id");
            $cost = $request->input("unitCost_$id");
            $remarks = $request->input("remarks_$id");

            $qty = (int)$qty;
            $cost = (float)$cost;

            PlanLineItems::where('LineItemID', $id)->update([
                'MergedQty' => $qty,
                'EstimatedUnitCost' => $cost,
                'ChangeRemarks' => $remarks,
                'ModifiedOn' => now(),
                'ModifiedBy' => auth()->id(),
            ]);
            $updatedItem = PlanLineItems::find($id);
            activity()
                ->causedBy($user)
                ->performedOn($updatedItem)
                ->event('update')
                ->log("Updated draft item: LineItemID {$id}");
    }

        return redirect()->back()->with('success', 'Draft plan items updated successfully.');
    }

    public function deleteDraftItem($id)
    {
        $user = auth()->user();

        $item = PlanLineItems::find($id);
       // $this->authorize('delete', $item);
        if ($item) {
            $item->update([
                'DeletedBy' => $user->id,
                'DeletedOn' => now(),
            ]);

            $item->delete();

            activity()
                ->causedBy($user)
                ->performedOn($item)
                ->event('delete')
                ->log("Deleted draft item: LineItemID {$id}");
        }

        return redirect()->back()->with('success', 'Item removed successfully.');
    }

    public function create(){
        return view('procurement.procurementplan.planapproval.ammendplan.create');
    }
}
