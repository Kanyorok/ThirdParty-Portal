<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\PlanManualInputRequest;
use App\Models\Procurement\BudgetMaster;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\Item;
use App\Models\Procurement\ItemCategory;
use App\Models\Procurement\PlanLineItems;
use Carbon\Carbon;
use Illuminate\Http\Request;


class PlanManualInputController extends Controller
{
    //
    public function index(Request $request)
    {
        $planId = $request->query('plan_id');//todo pass plan id from url
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
        $items = Item::with('category')->get();
        $budgetLines = BudgetMaster::all();

        return view('procurement.procurementplan.planconsolidation.manualentry.create', compact('plans', 'items', 'budgetLines'));
    }

    public function store(PlanManualInputRequest $request)
    {

        $validated = $request->validated();
        $user = $request->user();

        // Fetch the related models (optional, can skip if you just want to save foreign keys)
        $item = $request->getItem();
        $category = $request->getCategory();//todo

        $planLineItem = new PlanLineItems();
        $planLineItem->PlanID = $validated['PlanID'];
        $planLineItem->ItemID = $item->Id;
        $planLineItem->CategoryID = $category->Id;
        $planLineItem->MergedQty = $validated['quantity'];
        $planLineItem->UnitOfMeasure = $validated['unit_of_measure'];
        $planLineItem->EstimatedUnitCost = $validated['estimated_cost'];
        $planLineItem->AdjustedCost = 0;
        $planLineItem->ProcurementMethod = 'Open Tender';
        $planLineItem->SchedulePeriod = $validated['schedule_period'];
        $planLineItem->ExpectedDeliveryDate = $validated['expected_delivery_date'];
        $planLineItem->BudgetLineID = $validated['budget_line_id'];
        $planLineItem->ExecutionStatus = 'Pending';
        $planLineItem->ChangeRemarks = $validated['notes'] ?? null;
        $planLineItem->BranchID = $user->employee->BranchId;
        $planLineItem->DepartmentID = $user->employee->DepartmentId;
        $planLineItem->IsDeleted = 0;
        $planLineItem->CreatedBy = $user->id ?? 1;
        $planLineItem->ModifiedBy = $user->id ?? 1;
        $planLineItem->CreatedOn = Carbon::now();
        $planLineItem->ModifiedOn = Carbon::now();

        $planLineItem->save();

        activity()->causedBy($user)->performedOn($planLineItem)->event('create')->log('created plan line item ' . $planLineItem->ItemID);

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

    public function update(PlanManualInputRequest $request, $lineItemId)
    {
        $validated = $request->validated();

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

        activity()->causedBy($user)->performedOn($lineItem)->event('update')->log('updated plan line item ' . $lineItem->ItemID);

        return redirect()->route('procurement.procurementplan.planconsolidation.manualentry.index')->with('success', 'Line item updated successfully.');
    }

    public function destroy($lineItemId)
    {
        $lineItem = PlanLineItems::findOrFail($lineItemId);
        $lineItem->delete();
        activity()->causedBy($user)->performedOn($lineItem)->event('deleted')->log('deleted plan line item ' . $lineItem->ItemID);

        return redirect()->route('procurement.procurementplan.planconsolidation.manualentry.index')->with('success', 'Line item deleted successfully.');
    }

}
