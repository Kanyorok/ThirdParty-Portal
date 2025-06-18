<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Enums\ProcurementPlanStatusEnum;
use App\Models\Core\Workflow;

class ProcurementApprovalController extends Controller
{
    public function index(Request $request)
    {
        $draftPlans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Submitted)->get();

        $selectedPlan = null;
        if ($request->has('PlanID') && !empty($request->PlanID)) {
            $selectedPlan = ConsolidatedProcurementPlan::find($request->PlanID);

            if ($selectedPlan) {
                $selectedPlan->load(['lineItems.branch', 'lineItems.department', 'lineItems.item', 'lineItems.budgetLine','lineItems.procurementMode']);
                $selectedPlan->CurrentApprLevel = $this->getApprovalLevelFromStatus($selectedPlan->Status);
            } else {
                return redirect()->back()->with('error', 'Selected plan not found.');
            }
        }

        return view('procurement.procurementplan.planapproval.approve.index', compact('draftPlans', 'selectedPlan'));
    }

    private function getApprovalLevelFromStatus($status)
    {
        return match ($status) {
            ProcurementPlanStatusEnum::Draft => 'Level 1',
            ProcurementPlanStatusEnum::Submitted => 'Submitted Awaiting Approval',
            ProcurementPlanStatusEnum::Approved => 'Already Approved',
            ProcurementPlanStatusEnum::Rejected => 'Plan Rejected',
            default => 'N/A',
        };
    }

    public function submitDecision(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'planId' => 'required|integer',
            'role' => 'required|string',
            'action' => 'required|in:APPROVED,REJECTED,RETURNED',
            'comments' => 'required|string|max:1000'
        ]);

        $plan = ConsolidatedProcurementPlan::findOrFail($request->planId);

        switch ($request->action) {
            case 'APPROVED':
                $plan->Status = ProcurementPlanStatusEnum::Approved;
                break;
            case 'REJECTED':
                $plan->Status = ProcurementPlanStatusEnum::Rejected;
                break;
            case 'RETURNED':
                $plan->Status = ProcurementPlanStatusEnum::Draft;
                break;
        }

        $plan->Remarks = $request->comments;
        $plan->ModifiedBy = auth()->id();
        $plan->ModifiedOn = Carbon::now();
        $plan->save();

        // Update or insert Workflow record
        Workflow::create([
            'Source' => 'ProcurementPlan',
            'SourceID' => $plan->PlanID,
            'Stage' => $this->getApprovalLevelFromStatus($plan->Status),
            'Status' => match ($request->action) {
                'APPROVED' => 'Ap',
                'REJECTED' => 'Re',
                'RETURNED' => 'Dr',
            },
            'Notes' => $request->comments,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => Carbon::now(),
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($plan)
            ->event(strtolower($request->action))
            ->log("{$request->action} procurement plan (PlanID: {$plan->PlanID}) with comment: '{$request->comments}'");

        return redirect()->route('planning.approval.index')->with('success', 'Your decision has been recorded.');
    }
}
