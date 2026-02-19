<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\PlanManualInputRequest;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanManualInputController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PlanLineItem::class);
        $planId = $request->query('plan_id');//todo pass plan id from url
        $plans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Draft)->get();

        $lineItemsQuery = PlanLineItem::with(['item', 'item.category', 'item.uom']);

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

    public function create(Request $request)
    {
        $this->authorize('create', PlanLineItem::class);
        $selectedPlanId = $request->input('plan_id');
        $selectedPlanTitle = $request->input('title');
        $plans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Draft)->get();
        // Include items that have either a direct ItemPrice > 0 OR a related price ActualPrice > 0
        $items = ItemMasterList::with(['category', 'uom', 'price'])
            ->where(function ($q) {
                $q->whereNotNull('ItemPrice')->where('ItemPrice', '>', 0)
                  ->orWhereHas('price', function ($p) {
                      $p->whereNotNull('ActualPrice')->where('ActualPrice', '>', 0);
                  });
            })
            ->orderBy('ItemName')
            ->get();
        // Align with Approved Needs budget line source
        $budgetLines = \App\Models\Budget\BudgetLine::all();

        return view('procurement.procurementplan.planconsolidation.manualentry.create', compact('plans', 'items', 'budgetLines', 'selectedPlanId', 'selectedPlanTitle'));
    }

    public function store(PlanManualInputRequest $request)
    {
        $this->authorize('store', PlanLineItem::class);

        $validated = $request->validated();

        $user = $request->user();

        $item = $request->getItem();
        $category = $request->getCategory();//todo

        $existingItem = PlanLineItem::where('PlanID', $validated['PlanID'])
            ->where('ItemID', $item->Id)
            ->first();

        if ($existingItem) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['ItemID' => 'This item has already been added to the selected plan.']);
        }

        $planLineItem = new PlanLineItem();
        $planLineItem->PlanID = $validated['PlanID'];
        $planLineItem->ItemID = $item->Id;
        $planLineItem->CategoryID = $category->Id;
        $planLineItem->MergedQty = $validated['quantity'];
        $planLineItem->UnitOfMeasure = $validated['unit_of_measure_id'];
        $planLineItem->EstimatedUnitCost = $validated['estimated_cost'];
        $planLineItem->AdjustedCost = 0;
        $planLineItem->ProcurementMethod = '';
        $planLineItem->SchedulePeriod = $validated['schedule_period'];
        $planLineItem->ExpectedDeliveryDate = $validated['expected_delivery_date'];
        $planLineItem->BudgetLineID = isset($validated['budget_line_id']) && $validated['budget_line_id'] !== null
            ? (int)$validated['budget_line_id']
            : null;
        $planLineItem->ExecutionStatus = 'Pending';
        $planLineItem->ChangeRemarks = $validated['notes'] ?? null;
        $planLineItem->BranchID = $user->employee->BranchID;
        $planLineItem->DepartmentID = $user->employee->DepartmentID;
        $planLineItem->IsDeleted = 0;
        $planLineItem->CreatedBy = $user->id ?? 1;
        $planLineItem->ModifiedBy = $user->id ?? 1;
        $planLineItem->CreatedOn = Carbon::now();
        $planLineItem->ModifiedOn = Carbon::now();
        $planLineItem->SourceType = 'manual';
        $planLineItem->OriginalQTY = $validated['quantity'];

        $planLineItem->save();

        activity()->causedBy($user)->performedOn($planLineItem)->event('create')->log('created plan line item ' . $planLineItem->ItemID);

        return redirect()
            ->route('procurement.procurementplan.planconsolidation.manualentry.index', ['plan_id' => $validated['PlanID']])
            ->with('success', 'Line item added successfully.');
    }

    public function edit($lineItemId)
    {
        $lineItem = PlanLineItem::with(['item', 'item.category', 'item.uom'])->findOrFail($lineItemId);
        $this->authorize('edit', $lineItem);
        $plans = ConsolidatedProcurementPlan::all();
        $items = ItemMasterList::with(['category', 'uom', 'price'])
            ->where(function ($q) {
                $q->whereNotNull('ItemPrice')->where('ItemPrice', '>', 0)
                  ->orWhereHas('price', function ($p) {
                      $p->whereNotNull('ActualPrice')->where('ActualPrice', '>', 0);
                  });
            })
            ->orderBy('ItemName')
            ->get();
        $categories = ItemCategories::all();
        // Align with Approved Needs budget line source
        $budgetLines = \App\Models\Budget\BudgetLine::all();

        return view('procurement.procurementplan.planconsolidation.manualentry.edit', compact('lineItem', 'plans', 'items', 'categories', 'budgetLines'));
    }

    public function update(PlanManualInputRequest $request, $lineItemId)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $lineItem = PlanLineItem::findOrFail($lineItemId);

        $this->authorize('update', $lineItem);

        foreach ($validated as $key => $value) {
            switch ($key) {
                case 'PlanID':
                    $lineItem->PlanID = $value;

                    break;
                case 'ItemID':
                    $lineItem->ItemID = $value;

                    break;
                case 'CategoryID':
                    $lineItem->CategoryID = $value;

                    break;
                case 'quantity':
                    $lineItem->MergedQty = $value;

                    break;
                case 'unit_of_measure':
                    $lineItem->UnitOfMeasure = $value;

                    break;
                case 'estimated_cost':
                    $lineItem->EstimatedUnitCost = $value;

                    break;
                case 'schedule_period':
                    $lineItem->SchedulePeriod = $value;

                    break;
                case 'expected_delivery_date':
                    $lineItem->ExpectedDeliveryDate = $value;

                    break;
                case 'budget_line_id':
                    $lineItem->BudgetLineID = $value;

                    break;
                case 'notes':
                    $lineItem->ChangeRemarks = $value;

                    break;
            }
        }

        $lineItem->ModifiedOn = now();
        $lineItem->ModifiedBy = Auth::id();

        $lineItem->save();

        activity()
            ->causedBy($user)
            ->performedOn($lineItem)
            ->event('update')
            ->log('updated plan line item ' . $lineItem->ItemID);

        return redirect()
            ->route('procurement.procurementplan.planconsolidation.manualentry.index', ['plan_id' => $lineItem->PlanID])
            ->with('success', 'Line item updated successfully.');
    }

    public function destroy(Request $request, $lineItemId)
    {
        $user = $request->user();
        $lineItem = PlanLineItem::findOrFail($lineItemId);
        $this->authorize('delete', $lineItem);
        $lineItem->delete();
        activity()->causedBy($user)->performedOn($lineItem)->event('deleted')->log('deleted plan line item ' . $lineItem->ItemID);

        return redirect()
            ->route('procurement.procurementplan.planconsolidation.manualentry.index', ['plan_id' => $lineItem->PlanID])
            ->with('success', 'Line item deleted successfully.');
    }
}
