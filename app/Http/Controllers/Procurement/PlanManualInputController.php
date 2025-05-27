<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;
use App\Models\Procurement\Item;
use App\Models\Procurement\ItemCategory;
use Illuminate\Support\Facades\Auth;
use App\Traits\Model\UserActorTrait;
use App\Models\Auth\User;
use Carbon\Carbon;
use App\Models\Procurement\BudgetMaster;


class PlanManualInputController extends Controller
{
    //
   public function index(Request $request)
    {
        $planId = $request->query('plan_id');
        $plans = ConsolidatedProcurementPlan::all();

        $lineItemsQuery = PlanLineItems::with(['item', 'item.category']); 

        if ($planId) {
            $lineItemsQuery->where('PlanID', $planId);
        } elseif ($plans->isNotEmpty()) {
            $lineItemsQuery->where('PlanID', $plans->first()->PlanID);
            $planId = $plans->first()->PlanID;
        } else {
            $lineItemsQuery->whereNull('PlanID');
        }

        $lineItems = $lineItemsQuery->get();

        return view('procurement.procurementplan.planconsolidation.manualentry.index', compact('plans', 'lineItems', 'planId'));
    }

    public function create()
    {
        $plans = ConsolidatedProcurementPlan::all();
        $items = Item::all(); 
        $categories = ItemCategory::all();
        $budgetLines = BudgetMaster::all();

        return view('procurement.procurementplan.planconsolidation.manualentry.create', compact('plans', 'items', 'categories', 'budgetLines'));
    }

   public function store(Request $request)
    {
        $validated = $request->validate([
            'PlanID' => 'required|exists:t_ConsolidatedProcurementPlan,PlanID',
            'ItemID' => 'required|exists:t_items,Id',
            'CategoryID' => 'required|exists:t_ItemCategories,Id',
            'quantity' => 'required|integer|min:1',
            'unit_of_measure' => 'required|string|max:50',
            'estimated_cost' => 'required|numeric|min:0',
            'schedule_period' => 'required|string|max:10',
            'expected_delivery_date' => 'required|date',
            'budget_line_id' => 'required|integer',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // Fetch the related models (optional, can skip if you just want to save foreign keys)
        $item = Item::findOrFail($validated['ItemID']);
        $category = ItemCategory::findOrFail($validated['CategoryID']);

        $planLineItem = new PlanLineItems();
        $planLineItem->PlanID = $validated['PlanID'];
        $planLineItem->ItemID = $item->Id;
        $planLineItem->CategoryID = $category->Id; // Add this if your table/model has CategoryID field
        $planLineItem->MergedQty = $validated['quantity'];
        $planLineItem->UnitOfMeasure = $validated['unit_of_measure'];
        $planLineItem->EstimatedUnitCost = $validated['estimated_cost'];
        $planLineItem->AdjustedCost = 0;
        $planLineItem->ProcurementMethod = 'Open Tender'; // You can make this dynamic later
        $planLineItem->SchedulePeriod = $validated['schedule_period'];
        $planLineItem->ExpectedDeliveryDate = $validated['expected_delivery_date'];
        $planLineItem->BudgetLineID = $validated['budget_line_id'];
        $planLineItem->ExecutionStatus = 'Pending';
        $planLineItem->ChangeRemarks = $validated['notes'] ?? null;
        $planLineItem->BranchID=$user->employee->BranchId;
        $planLineItem->DepartmentID=$user->employee->DepartmentId;
        $planLineItem->IsDeleted = 0;
        $planLineItem->CreatedBy = $user->id??1;
        $planLineItem->ModifiedBy = $user->id??1;
        $planLineItem->CreatedOn = Carbon::now();
        $planLineItem->ModifiedOn = Carbon::now();

        //dd($planLineItem->BranchID);
        $planLineItem->save();

        return redirect()->route('procurement.procurementplan.planconsolidation.manualentry.index')->with('success', 'Line item added successfully.');
    }
    public function edit($lineItemId)
{
    $lineItem = PlanLineItems::with(['item', 'item.category'])->findOrFail($lineItemId);
    $plans = ConsolidatedProcurementPlan::all();
    $items = Item::all();
    $categories = ItemCategory::all();
    $budgetLines = BudgetMaster::all();

    return view('procurement.procurementplan.planconsolidation.manualentry.edit', compact('lineItem', 'plans', 'items', 'categories', 'budgetLines'));
}

public function update(Request $request, $lineItemId)
{
    $validated = $request->validate([
        'PlanID' => 'required|exists:t_ConsolidatedProcurementPlan,PlanID',
        'ItemID' => 'required|exists:t_items,Id',
        'CategoryID' => 'required|exists:t_ItemCategories,Id',
        'quantity' => 'required|integer|min:1',
        'unit_of_measure' => 'required|string|max:50',
        'estimated_cost' => 'required|numeric|min:0',
        'schedule_period' => 'required|string|max:10',
        'expected_delivery_date' => 'required|date',
        'budget_line_id' => 'required|integer',
        'notes' => 'nullable|string|max:1000',
    ]);

    $lineItem = PlanLineItems::findOrFail($lineItemId);

    $lineItem->PlanID = $validated['PlanID'];
    $lineItem->ItemID = $validated['ItemID'];
    $lineItem->CategoryID = $validated['CategoryID'];
    $lineItem->MergedQty = $validated['quantity'];
    $lineItem->UnitOfMeasure = $validated['unit_of_measure'];
    $lineItem->EstimatedUnitCost = $validated['estimated_cost'];
    $lineItem->SchedulePeriod = $validated['schedule_period'];
    $lineItem->ExpectedDeliveryDate = $validated['expected_delivery_date'];
    $lineItem->BudgetLineID = $validated['budget_line_id'];
    $lineItem->ChangeRemarks = $validated['notes'] ?? null;
    $lineItem->ModifiedOn = Carbon::now();
    $lineItem->ModifiedBy = auth()->id();

    $lineItem->save();

    return redirect()->route('procurement.procurementplan.planconsolidation.manualentry.index')->with('success', 'Line item updated successfully.');
}
public function destroy($lineItemId)
{
    $lineItem = PlanLineItems::findOrFail($lineItemId);
    $lineItem->delete();

    return redirect()->route('procurement.procurementplan.planconsolidation.manualentry.index')->with('success', 'Line item deleted successfully.');
}

}