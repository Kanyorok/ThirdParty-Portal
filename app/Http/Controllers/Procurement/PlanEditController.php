<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\PlanLineItem;
use App\Enums\ProcurementPlanStatusEnum;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Policies\Procurement\PlanEditPolicy;
use Illuminate\Support\Facades\Auth;

class PlanEditController extends Controller
{
    //
    public function index(Request $request)
    {
        $this->authorize('viewAny', PlanLineItem::class);
        $planId = $request->input('PlanID');

        $availablePlans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Draft)->get();

        $draftItems = collect();
        if ($planId) {
            $draftItems = PlanLineItem::where('PlanID', $planId)
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

        $user = Auth::user();
        $itemIds = $request->input('lineItemIds', []);
        $errors = [];

        foreach ($itemIds as $id) {
            $item = PlanLineItem::findOrFail($id);
            $this->authorize('update', $item);
            $qty = $request->input("qty_$id");
            $cost = $request->input("unitCost_$id");
            $remarks = $request->input("remarks_$id");

            $qty = (int)$qty;
            $cost = (float)$cost;

            // Enforce upper bound only for non-manual items
            $isManual = strtolower((string)($item->SourceType ?? '')) === 'manual';
            if (!$isManual && $qty > $item->OriginalQTY) {
                $errors[] = "Cannot set quantity for item '{$item->item->ItemName}' (ID: $id) greater than original quantity ({$item->OriginalQTY}).";
                continue;
            }

            PlanLineItem::where('LineItemID', $id)->update([
                'MergedQty' => $qty,
                'EstimatedUnitCost' => $cost,
                'ChangeRemarks' => $remarks,
                'ModifiedOn' => now(),
                'ModifiedBy' => Auth::user()?->Id,
            ]);
            $updatedItem = PlanLineItem::find($id);
            activity()
                ->causedBy($user)
                ->performedOn($updatedItem)
                ->event('update')
                ->log("Updated draft item: LineItemID {$id}");
    }
        if (count($errors) > 0) {
            return redirect()->back()->with('error', implode(' ', $errors));
        }

        return redirect()->back()->with('success', 'Draft plan items updated successfully.');
    }

    public function deleteDraftItem($id)
    {
        $user = Auth::user();

        $item = PlanLineItem::find($id);
        $this->authorize('delete', $item);
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
