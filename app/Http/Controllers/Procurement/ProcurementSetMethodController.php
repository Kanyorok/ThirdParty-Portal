<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;
use App\Services\Procurement\ProcurementPlan\ProcurementMethodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ProcurementSetMethodController extends Controller
{
    //
    public function index()
    {
        $approvedPlans = ConsolidatedProcurementPlan::where('Status', 'd')->get();
        return view('procurement.procurementplan.planneditemsandactivities.assignprocurementmethod.index', compact('approvedPlans'));
    }

    public function create(){
        //return view('procurement.procurementplan.planneditemsandactivities.assignprocurementmethod.create');
    }
    public function getPlanItems($planId)
    {
        $Lines = PlanLineItems::with('item')->where('PlanID', $planId)->get()
            ->map(function ($lineItem) {
                return [
                    'LineItemID' => $lineItem->LineItemID,
                    'item_name' => optional($lineItem->item)->ItemName,
                    'MergedQty' => $lineItem->MergedQty,
                    'EstimatedUnitCost' => $lineItem->EstimatedUnitCost,
                    'ProcurementMethod' => $lineItem->ProcurementMethod,
                ];
            });
        return response()->json($Lines);
    }

public function store(Request $request, ProcurementMethodService $service)
{
    //dd($request->all());
    $request->validate([
        'approved_plan_id' => 'required|exists:t_ConsolidatedProcurementPlan,PlanID',
        'assigned_method' => 'required|array',
        'justification' => 'nullable|array',
    ]);

    $planId = $request->input('approved_plan_id');
    $assignedMethods = $request->input('assigned_method');
    $justifications = $request->input('justification');

    $plan = ConsolidatedProcurementPlan::findOrFail($planId);
    $user = auth()->user();

    foreach ($assignedMethods as $lineItemId => $method) {
        $lineItem = PlanLineItems::find($lineItemId);

        if ($lineItem) {
            $service->create([
                'AssignedMethod' => $method,
                'Justification' => $justifications[$lineItemId] ?? '',
                'EstimatedUnitCost' => $lineItem->EstimatedUnitCost,
            ], $user, $plan, $lineItem);
        }
    }

    return redirect()->back()->with('success', 'Procurement methods saved successfully.');
}

}
