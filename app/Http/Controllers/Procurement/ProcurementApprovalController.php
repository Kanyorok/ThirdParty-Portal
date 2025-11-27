<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Services\Procurement\ProcurementPlan\ConsolidatedPlanWorkflowService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProcurementApprovalController extends Controller
{
    /**
     * Display list of plans pending approval for current user
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Get plans where user has pending approval using workflow service
        $pendingPlans = DB::table('t_ConsolidatedProcurementPlan as p')
            ->join('t_WorkFlowPending as wp', function($join) {
                $join->on('wp.Source', '=', DB::raw("'t_ConsolidatedProcurementPlan'"))
                     ->on('wp.SourceID', '=', DB::raw('CAST(p.PlanID AS VARCHAR)'));
            })
            ->where('wp.UserId', $user->Id)
            ->where('p.Status', ProcurementPlanStatusEnum::Pending->value)
            ->whereNull('wp.DeletedOn')
            ->whereNull('p.DeletedOn')
            ->select('p.*')
            ->distinct()
            ->get();

        // Convert to Eloquent collection
        $draftPlans = ConsolidatedProcurementPlan::whereIn('PlanID', $pendingPlans->pluck('PlanID'))->get();

        $selectedPlan = null;
        $workflowStatus = null;
        $workflowHistory = null;
        $canApprove = false;

        if ($request->has('PlanID') && !empty($request->PlanID)) {
            $selectedPlan = ConsolidatedProcurementPlan::find($request->PlanID);

            if ($selectedPlan) {
                // Load relationships
                $selectedPlan->load([
                    'lineItems.branch', 
                    'lineItems.department', 
                    'lineItems.item', 
                    'lineItems.budgetLine', 
                    'lineItems.procurementMode',
                    'createdBy',
                    'submittedBy'
                ]);

                // Get workflow service for this plan
                $workflowService = new ConsolidatedPlanWorkflowService($selectedPlan);

                // Check if user can approve
                $canApprove = $workflowService->canUserApprove($user);

                // Get workflow status
                $workflowStatus = $workflowService->getWorkflowStatus();

                // Get workflow history
                $workflowHistory = $workflowService->getHistory();

                // Set current approval level for display
                $selectedPlan->CurrentApprLevel = $this->getApprovalLevelFromWorkflow($workflowStatus);
            } else {
                return redirect()->back()->with('error', 'Selected plan not found.');
            }
        }

        return view('procurement.procurementplan.planapproval.approve.index', compact(
            'draftPlans', 
            'selectedPlan', 
            'workflowStatus', 
            'workflowHistory',
            'canApprove'
        ));
    }

    /**
     * Get approval level description from workflow status
     */
    private function getApprovalLevelFromWorkflow(?array $workflowStatus): string
    {
        if (!$workflowStatus || !$workflowStatus['hasWorkflow']) {
            return 'N/A';
        }

        $currentStage = $workflowStatus['currentStage'] ?? null;
        if (!$currentStage) {
            return 'N/A';
        }

        $pendingCount = $workflowStatus['totalPending'] ?? 0;
        $completedCount = $workflowStatus['totalCompleted'] ?? 0;

        return sprintf(
            '%s (Stage %d) - %d of %d approvals completed',
            $currentStage['name'],
            $currentStage['order'],
            $completedCount,
            $pendingCount + $completedCount
        );
    }

    /**
     * Legacy method for backward compatibility
     */
    private function getApprovalLevelFromStatus($status): string
    {
        return match ($status) {
            ProcurementPlanStatusEnum::Draft => 'Draft - Not Submitted',
            ProcurementPlanStatusEnum::Pending => 'Pending Approval',
            ProcurementPlanStatusEnum::Approved => 'Fully Approved',
            ProcurementPlanStatusEnum::Rejected => 'Rejected',
            default => 'N/A',
        };
    }

    /**
     * Submit approval decision
     */
    public function submitDecision(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $request->validate([
            'planId' => 'required|integer',
            'action' => 'required|in:APPROVED,REJECTED',
            'comments' => 'required|string|max:1000'
        ]);

        $lock = Cache::lock('approve-plan-' . $request->planId, 5);
        if (!$lock->get()) {
            return redirect()
                ->back()
                ->with('error', 'Another user is processing this plan. Please try again.');
        }

        try {
            $plan = ConsolidatedProcurementPlan::findOrFail($request->planId);
            $workflowService = new ConsolidatedPlanWorkflowService($plan);

            // Check if user can approve
            if (!$workflowService->canUserApprove($user)) {
                $lock->release();
                return redirect()->back()->with('error', 'You do not have permission to approve this plan.');
            }

            DB::beginTransaction();

            if ($request->action === 'APPROVED') {
                $workflowService->approve($user, $request->comments);
                $actionMessage = 'approved';
                $logEvent = 'approved';
            } else {
                $workflowService->reject($user, $request->comments);
                $actionMessage = 'rejected';
                $logEvent = 'rejected';
            }

            // Log activity
            activity()
                ->causedBy($user)
                ->performedOn($plan)
                ->event($logEvent)
                ->log("{$actionMessage} procurement plan (PlanID: {$plan->PlanID}) with comment: '{$request->comments}'");

            DB::commit();
            $lock->release();

            return redirect()
                ->route('planning.approval.index')
                ->with('success', "Plan {$actionMessage} successfully.");

        } catch (ErroredException $e) {
            DB::rollBack();
            $lock->release();
            Log::error('Approval decision error', [
                'planId' => $request->planId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', $e->getMessage());
            
        } catch (Exception $e) {
            DB::rollBack();
            $lock->release();
            Log::error('Unexpected error in approval decision', [
                'planId' => $request->planId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * View detailed plan information for approval
     */
    public function show(Request $request, int $planId): View
    {
        $user = $request->user();
        
        $plan = ConsolidatedProcurementPlan::with([
            'lineItems.branch', 
            'lineItems.department', 
            'lineItems.item', 
            'lineItems.budgetLine', 
            'lineItems.procurementMode',
            'createdBy',
            'submittedBy'
        ])->findOrFail($planId);

        $workflowService = new ConsolidatedPlanWorkflowService($plan);

        // Check if user can approve
        $canApprove = $workflowService->canUserApprove($user);

        // Get workflow status
        $workflowStatus = $workflowService->getWorkflowStatus();

        // Get workflow history
        $workflowHistory = $workflowService->getHistory();

        // Calculate total amount
        $totalAmount = $plan->lineItems->sum(function ($item) {
            return $item->Quantity * $item->EstimatedUnitPrice;
        });

        return view('procurement.procurementplan.planapproval.approve.show', compact(
            'plan',
            'canApprove',
            'workflowStatus',
            'workflowHistory',
            'totalAmount'
        ));
    }

    /**
     * Cancel/withdraw plan from approval workflow (for submitter only)
     */
    public function cancel(Request $request, int $planId): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $lock = Cache::lock('cancel-plan-' . $planId, 5);
        if (!$lock->get()) {
            return redirect()
                ->back()
                ->with('error', 'Another user is processing this plan. Please try again.');
        }

        $actor = $request->user();
        $plan = ConsolidatedProcurementPlan::findOrFail($planId);

        try {
            DB::beginTransaction();
            
            $workflowService = new ConsolidatedPlanWorkflowService($plan);
            $workflowService->cancel(
                $actor,
                $request->input('reason', 'Cancelled by submitter')
            );

            // Log activity
            activity()
                ->causedBy($actor)
                ->performedOn($plan)
                ->event('cancelled')
                ->log("Cancelled procurement plan workflow (PlanID: {$plan->PlanID})");

            DB::commit();
            $lock->release();

            return redirect()
                ->route('Procurement-Plan-Submission.index')
                ->with('success', 'Plan workflow cancelled. Plan returned to draft status.');

        } catch (ErroredException $e) {
            DB::rollBack();
            $lock->release();
            return redirect()->back()->with('error', $e->getMessage());
            
        } catch (Exception $e) {
            DB::rollBack();
            $lock->release();
            Log::error('Error cancelling plan workflow', [
                'planId' => $planId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Unexpected error occurred. Please try again.');
        }
    }

    /**
     * Get workflow status for AJAX requests
     */
    public function getWorkflowStatus(Request $request, int $planId)
    {
        try {
            $plan = ConsolidatedProcurementPlan::findOrFail($planId);
            $workflowService = new ConsolidatedPlanWorkflowService($plan);
            
            $status = $workflowService->getWorkflowStatus();
            $canApprove = $workflowService->canUserApprove($request->user());
            
            return response()->json([
                'success' => true,
                'status' => $status,
                'canApprove' => $canApprove,
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve workflow status'
            ], 500);
        }
    }
}