<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcurementPlanWorkflow extends ApprovalWorkflowService
{
    // CodeID for procurement plans in t_CodeDetails
    public const CODE_ID = 'ProcurementPlanStatus';

    /**
     * Get mapped status value from config
     */
    public function getMappedStatus(string $statusDescription): string
    {
        $mappings = config('workflow.PlanID', []);
        
        if (isset($mappings[$statusDescription])) {
            return $mappings[$statusDescription];
        }
        
        // Fallback to first character lowercase
        return strtolower(substr($statusDescription, 0, 1));
    }

    /**
     * Submit plan for approval
     */
    public function submit(
        ConsolidatedProcurementPlan $plan,
        User $actor,
        ProcurementPlanStatusEnum $status,
        string $remarks = 'Submitted for approval'
    ): bool {
        // Get the mapped status value (e.g., 'P' for Pending)
        $mappedStatusValue = $this->getMappedStatus('Pending');
        
        // Find the CodeDetail entry
        $codeDetail = CodeDetail::query()
            ->where('CodeID', self::CODE_ID)
            ->where('Value', $mappedStatusValue)
            ->first();

        if (!$codeDetail) {
            Log::error("CodeDetail not found for Pending status", [
                'codeId' => self::CODE_ID,
                'mappedValue' => $mappedStatusValue,
            ]);
            throw new ErroredException('Invalid status configuration for Pending');
        }

        Log::info("Found CodeDetail", [
            'id' => $codeDetail->ID,
            'value' => $codeDetail->Value,
            'description' => $codeDetail->Description,
        ]);

        Log::info("ProcurementPlanWorkflow: Submitting plan", [
            'planId' => $plan->PlanID,
            'table' => $plan->getTable(),
            'pendingStatus' => $status->value,
            'mappedStatus' => $mappedStatusValue,
            'codeDetailId' => $codeDetail->ID,
        ]);

        // Calculate total amount for workflow routing
        $totalAmount = $plan->lineItems->sum(function ($item) {
            $quantity = (float) ($item->MergedQty ?? $item->OriginalQTY ?? 0);
            $unitCost = ($item->AdjustedCost > 0)
                ? $item->AdjustedCost
                : ($item->EstimatedUnitCost ?? 0);
            return $quantity * $unitCost;
        });

        // Temporarily set the amount on the model for workflow processing
        $plan->Amount = $totalAmount;

        // Use parent's submittedAction method
        $result = $this->submittedAction(
            $actor,
            $codeDetail,
            $plan,
            'PlanID', // morph alias
            $plan->PlanID,
            $remarks
        );

        if ($result) {
            Log::info("Workflow history created", [
                'table' => $plan->getTable(),
                'sourceId' => $plan->PlanID,
                'stageId' => $this->getCurrentStageId($plan->getTable(), $plan->PlanID),
                'statusId' => $codeDetail->ID,
            ]);
            
            Log::info("Pending approvers created successfully");
        }

        return $result;
    }

    /**
     * Approve plan
     */
    public function approve(
        ConsolidatedProcurementPlan $plan,
        User $actor,
        ProcurementPlanStatusEnum $status,
        string $remarks = 'Approved'
    ): bool {
        // Get the mapped status value (e.g., 'Ap' for Approved)
        $mappedStatusValue = $this->getMappedStatus('Approved');
        
        // Find the CodeDetail entry
        $codeDetail = CodeDetail::query()
            ->where('CodeID', self::CODE_ID)
            ->where('Value', $mappedStatusValue)
            ->first();

        if (!$codeDetail) {
            Log::error("CodeDetail not found for Approved status", [
                'codeId' => self::CODE_ID,
                'mappedValue' => $mappedStatusValue,
            ]);
            throw new ErroredException('Invalid status configuration for Approved');
        }

        Log::info("Found CodeDetail", [
            'id' => $codeDetail->ID,
            'value' => $codeDetail->Value,
            'description' => $codeDetail->Description,
        ]);

        Log::info("ProcurementPlanWorkflow: Approving plan", [
            'planId' => $plan->PlanID,
            'approvedStatus' => $status->value,
            'mappedStatus' => $mappedStatusValue,
            'codeDetailId' => $codeDetail->ID,
        ]);

        // Use parent's approveAction method
        return $this->approveAction(
            $actor,
            $codeDetail,
            'PlanID', // morph alias
            $plan->PlanID,
            $remarks,
            'Status'
        );
    }

    /**
     * Reject plan
     */
    public function reject(
        ConsolidatedProcurementPlan $plan,
        User $actor,
        ProcurementPlanStatusEnum $status,
        string $remarks = 'Rejected'
    ): bool {
        // Get the mapped status value (e.g., 'R' for Rejected)
        $mappedStatusValue = $this->getMappedStatus('Rejected');
        
        // Find the CodeDetail entry
        $codeDetail = CodeDetail::query()
            ->where('CodeID', self::CODE_ID)
            ->where('Value', $mappedStatusValue)
            ->first();

        if (!$codeDetail) {
            Log::error("CodeDetail not found for Rejected status", [
                'codeId' => self::CODE_ID,
                'mappedValue' => $mappedStatusValue,
            ]);
            throw new ErroredException('Invalid status configuration for Rejected');
        }

        Log::info("ProcurementPlanWorkflow: Rejecting plan", [
            'planId' => $plan->PlanID,
            'rejectedStatus' => $status->value,
            'mappedStatus' => $mappedStatusValue,
            'codeDetailId' => $codeDetail->ID,
        ]);

        // Use parent's rejectAction method
        return $this->rejectAction(
            $actor,
            $codeDetail,
            'PlanID', // morph alias
            $plan->PlanID,
            $remarks,
            'Status'
        );
    }

    /**
     * Cancel/withdraw plan from workflow
     */
    public function cancel(
        ConsolidatedProcurementPlan $plan,
        User $actor,
        string $reason = 'Cancelled by submitter'
    ): bool {
        return $this->cancelWorkflow(
            $actor,
            'PlanID', // morph alias
            $plan->PlanID,
            $reason
        );
    }

    /**
     * Get workflow history for plan
     */
    public function historyForModel(ConsolidatedProcurementPlan $plan): Collection
    {
        return $this->historyData('PlanID', 1000);
    }

    /**
     * Check if user can approve this plan
     */
    public function canApproveModel(ConsolidatedProcurementPlan $plan, User $user): bool
    {
        return $this->canApprove(
            'PlanID', // morph alias
            $plan->PlanID,
            $user
        );
    }

    /**
     * Get workflow status for plan
     */
    public function getStatus(ConsolidatedProcurementPlan $plan): array
    {
        return $this->getWorkflowStatus(
            'PlanID', // morph alias
            $plan->PlanID
        );
    }
}