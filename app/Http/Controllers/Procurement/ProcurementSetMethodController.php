<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItem;
use App\Services\Procurement\ProcurementPlan\ProcurementMethodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcurementSetMethodController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ConsolidatedProcurementPlan::class);
        $approvedPlans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Draft)->get();
        $procurementModes = CodeDetail::where('CodeID', 'ProcurementMethod')->get();

        return view('procurement.procurementplan.planneditemsandactivities.assignprocurementmethod.index', compact('approvedPlans', 'procurementModes'));
    }

    public function create()
    {
    }

    public function getPlanItems($planId)
    {
        $Lines = PlanLineItem::with(['item', 'procurementMode', 'departmentNeed'])
            ->where('PlanID', $planId)
            ->get()
            ->map(function ($lineItem) {
                // Find matching DepartmentNeed based on business rules
                $matchedNeed = $lineItem->departmentNeed()
                    ->where('BranchID', $lineItem->BranchID)
                    ->where('DepartmentID', $lineItem->DepartmentID)
                    ->whereNull('DeletedOn')
                    ->first();

                return [
                    'LineItemID' => $lineItem->LineItemID,
                    'item_name' => optional($lineItem->item)->ItemName,
                    'MergedQty' => $lineItem->MergedQty,
                    'EstimatedUnitCost' => $lineItem->EstimatedUnitCost,
                    'ProcurementMethod' => optional($lineItem->procurementMode)->Description,
                    'NeedID' => optional($matchedNeed)->NeedID,
                ];
            });

        return response()->json($Lines);
    }

    public function store(Request $request, ProcurementMethodService $service)
    {
        // Explicitly check for permission to assign methods
        $this->authorize(\App\Enums\Core\PermissionEnum::ProcurementMethodWrite->value);

        $request->validate([
            'approved_plan_id' => 'required|exists:t_ConsolidatedProcurementPlan,PlanID',
            'assigned_method' => 'required|array',
            'justification' => 'nullable|array',
        ]);

        $planId = $request->input('approved_plan_id');
        $assignedMethods = $request->input('assigned_method');
        $justifications = $request->input('justification');

        $user = Auth::user();
        $plan = ConsolidatedProcurementPlan::find($planId);

        foreach ($assignedMethods as $lineItemId => $method) {
            if ($method && $method !== '') {
                $lineItem = PlanLineItem::find($lineItemId);

                if ($method && $lineItem) {
                    $lineItem->ProcurementMethod = $method;
                    $lineItem->save();
                    $service->create([
                        'AssignedMethod' => $method,
                        'Justification' => $justifications[$lineItemId] ?? '',
                        'EstimatedUnitCost' => $lineItem->EstimatedUnitCost,
                    ], $user, $plan, $lineItem);
                }
            }
        }

        return redirect()->back()->with('success', 'Procurement methods saved successfully.');
    }
}
