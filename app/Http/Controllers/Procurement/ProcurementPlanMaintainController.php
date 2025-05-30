<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Enums\Core\PostingEnum;

class ProcurementPlanMaintainController extends Controller
{
    //
    public function index()
    {
        $plans = ConsolidatedProcurementPlan::with(['createdBy', 'lineItems'])->get();

        return view('procurement.procurementplan.procurementplanmaintenance.index', compact('plans'));
    }

    public function getEstimatedCostAttribute()
    {
        return $this->lineItems->sum(fn($item) => $item->MergedQty * $item->EstimatedUnitCost);
    }

    public function store(Request $request)
    {
        $request->validate([
            'Title' => 'required|string|max:255',
            'FiscalYear' => 'required|integer',
            'Status' => 'required|string',
            'CreatedBy' => 'required|integer',
        ]);

        $userId = $request->CreatedBy;

        $plan = ConsolidatedProcurementPlan::create([
            'Title' => $request->Title,
            'ReferenceNumber' => 'PLAN/' . $request->FiscalYear . '/' . rand(100, 999), // You may replace with a unique generator
            'FiscalYear' => $request->FiscalYear,
            'Status' => PostingEnum::Draft,
            'CreatedBy' => $userId,
            'SubmittedBy' => $userId,
            'CreatedDate' => now(),
            'SubmittedDate' => now(),
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
            'CurrentApprLevel' => 0,
            'ModifiedBy' => $userId,
        ]);

        // Redirect to manual entry page with the new plan ID
        return redirect()->route('planmanualinput.index', ['plan_id' => $plan->PlanID])
            ->with('success', 'Plan created successfully. You may now add line items.');
    }
    public function create(){
        return view('procurement.procurementplan.procurementplanmaintenance.create');
    }

}
