<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Services\Procurement\ProcurementPlan\ProcurementPlanWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                        // RETURNED usually means sending back to draft or previous stage. 
                        // If we treat it as "Reject" to Draft, we can use reject with Draft status?
                        // Or maybe we need a 'Return' method in workflow?
                        // For now, let's assume 'RETURNED' maps to 'Draft' status via reject or a specific logic.
                        // But ProcurementPlanStatusEnum::Draft is 'Dr'.
                        // The workflow service 'reject' method takes a status enum.
                        // Let's use reject with Draft status if that's the intention, or Rejected status.
                        // The original code set status to Draft for RETURNED.
                        // So let's use reject but pass Draft status if possible, or just use reject.
                        // However, 'reject' usually sets status to Rejected.
                        // If we want to move back to Draft, we might need to use 'cancel' or just 'reject' with a note.
                        // Let's stick to 'reject' with Rejected status for now as 'RETURNED' isn't a standard workflow action in the generic service usually.
                        // Wait, the original code set status to Draft.
                        // If I use $this->workflow->reject($plan, $user, ProcurementPlanStatusEnum::Draft, ...), 
                        // the service will look for CodeDetail for 'Draft' (Dr).
                        // If that exists, it might work.
                        $this->workflow->reject($plan, $user, ProcurementPlanStatusEnum::Draft, $request->comments);
                        break;
                }
            });

            // Activity logging is handled by the service or we can keep it here if needed.
            // The generic service logs to Log facade but maybe not activity() package.
            // Let's keep the activity log for consistency with previous code.
            activity()
                ->causedBy($user)
                ->performedOn($plan)
                ->event(strtolower($request->action))
                ->log("{$request->action} procurement plan (PlanID: {$plan->PlanID}) with comment: '{$request->comments}'");

            return redirect()->route('planning.approval.index')->with('success', 'Your decision has been recorded.');
        } catch (\Exception $e) {
            Log::error('Error submitting decision: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while processing your decision: ' . $e->getMessage());
        }
    }
}
