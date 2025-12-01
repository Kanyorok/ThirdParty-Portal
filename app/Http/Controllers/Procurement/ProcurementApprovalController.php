<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Workflow;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use Carbon\Carbon;
use App\Services\Procurement\ProcurementPlan\ProcurementPlanWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ProcurementApprovalController extends Controller
{
    protected ProcurementPlanWorkflow $workflow;

    public function __construct(ProcurementPlanWorkflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function index(Request $request)
    {
        $draftPlans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Submitted)->get();

        $selectedPlan = null;
        if ($request->has('PlanID') && !empty($request->PlanID)) {
            $selectedPlan = ConsolidatedProcurementPlan::find($request->PlanID);

            if ($selectedPlan) {
                $selectedPlan->load(['lineItems.branch', 'lineItems.department', 'lineItems.item', 'lineItems.budgetLine', 'lineItems.procurementMode']);
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

        try {
            DB::transaction(function () use ($request, $plan, $user) {
                switch ($request->action) {
                    case 'APPROVED':
                        $this->workflow->approve($plan, $user, ProcurementPlanStatusEnum::Approved, $request->comments);
                        break;
                    case 'REJECTED':
                        $this->workflow->reject($plan, $user, ProcurementPlanStatusEnum::Rejected, $request->comments);
                        break;
                    case 'RETURNED':
                        $this->workflow->reject($plan, $user, ProcurementPlanStatusEnum::Draft, $request->comments);
                        break;
                }
            });

            activity()
                ->causedBy($user)
                ->performedOn($plan)
                ->event(strtolower($request->action))
                ->log("{$request->action} procurement plan (PlanID: {$plan->PlanID}) with comment: '{$request->comments}'");

            return redirect()->route('planning.approval.index')->with('success', 'Your decision has been recorded.');
        } catch (\Exception $e) {
            Log::error('Workflow decision failed', ['error' => $e->getMessage(), 'planId' => $plan->PlanID]);
            return redirect()->back()->with('error', 'Failed to process decision: ' . $e->getMessage());
        }
    }
}
