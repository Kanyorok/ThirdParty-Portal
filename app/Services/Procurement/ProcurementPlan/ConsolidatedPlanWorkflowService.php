<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;

use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsolidatedPlanWorkflowService
{

    private ConsolidatedProcurementPlan $plan;
    private ProcurementPlanWorkflow $workflow;

    public function __construct(ConsolidatedProcurementPlan $plan)
    {
        $this->plan = $plan;
        
        Log::info("ConsolidatedPlanWorkflowService: Constructor", [
            'planId' => $this->plan->PlanID,
            'planStatus' => $this->plan->Status->value ?? 'NULL',
        ]);
        
        // Use the new procurement-specific workflow service
        $this->workflow = new ProcurementPlanWorkflow();
    }

    /**
     * Submit plan for approval
     *
     * @param User $actor
     * @param string $remarks
     * @return bool
     * @throws ErroredException
     */
     public function submitForApproval(User $actor, string $remarks = 'Submitted for approval'): bool
{
    Log::info("Submitting Consolidated Procurement Plan for approval", [
        'planId' => $this->plan->PlanID,
        'actorId' => $actor->Id,
        'currentStatus' => $this->plan->Status->value ?? null,
    ]);

    // Validate that plan exists and has an ID
    if (is_null($this->plan->PlanID)) {
        throw new ErroredException('Invalid plan: PlanID is missing');
    }

    // Allow submission from Draft or Rejected status (for resubmission)
    $allowedStatuses = [
        ProcurementPlanStatusEnum::Draft,
        ProcurementPlanStatusEnum::Rejected
    ];
    
    if (!in_array($this->plan->Status, $allowedStatuses)) {
        throw new ErroredException(
            'Only draft or rejected plans can be submitted for approval. Current status: ' . 
            ($this->plan->Status->value ?? 'unknown')
        );
    }

    // REMOVED THE DUPLICATE CHECK THAT WAS HERE

    // Validate that plan has line items
    if ($this->plan->lineItems()->count() === 0) {
        throw new ErroredException('Cannot submit a plan without line items');
    }

    try {
        DB::beginTransaction();

        // If resubmitting (status was Rejected), clean up old workflow records
        if ($this->plan->Status === ProcurementPlanStatusEnum::Rejected) {
            DB::table('t_WorkflowPending')
                ->where('Source', $this->plan->getTable())
                ->where('SourceID', (string)$this->plan->PlanID)
                ->whereNull('DeletedOn')
                ->update([
                    'DeletedOn' => now(),
                    'DeletedBy' => $actor->Id,
                ]);
        }

        // Calculate total amount from line items (for workflow routing if needed)
        $totalAmount = $this->plan->lineItems->sum(function ($item) {
            $quantity = (float) ($item->MergedQty ?? $item->OriginalQTY ?? 0);
            $unitCost = ($item->AdjustedCost > 0)
               ? $item->AdjustedCost
               : ($item->EstimatedUnitCost ?? 0);
            return $quantity * $unitCost;
        });

        Log::info("Calculated total amount for workflow", [
            'planId' => $this->plan->PlanID,
            'totalAmount' => $totalAmount,
        ]);

        $this->logLineItemDetails();

        // Update plan status to Pending using the mapped value from config
        $pendingStatusValue = $this->workflow->getMappedStatus('Pending');
        $this->plan->Status = ProcurementPlanStatusEnum::from($pendingStatusValue);
        $this->plan->SubmittedBy = $actor->Id;
        $this->plan->SubmittedDate = now();
        $this->plan->ModifiedBy = $actor->Id;
        $this->plan->save();

        Log::info("Plan status updated using config mapping", [
            'planId' => $this->plan->PlanID,
            'status' => $this->plan->Status->value,
            'mappedValue' => $pendingStatusValue,
        ]);

        // Submit to workflow - the workflow service will use config mappings
        $result = $this->workflow->submit(
            $this->plan,
            $actor,
            ProcurementPlanStatusEnum::Pending,
            $remarks
        );

        if (!$result) {
            throw new ErroredException('Failed to submit plan to workflow');
        }

        $this->logPendingApprovers();

        DB::commit();

        Log::info("Plan submitted successfully", ['planId' => $this->plan->PlanID]);
        return true;

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("Failed to submit plan for approval", [
            'planId' => $this->plan->PlanID,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw new ErroredException($e->getMessage());
    }
}
     /**
     * Log detailed line item information for debugging
     */
    private function logLineItemDetails(): void
    {
        $lineItems = $this->plan->lineItems()->with(['item', 'branch', 'department'])->get();
        
        Log::info("Plan Line Items Details", [
            'planId' => $this->plan->PlanID,
            'totalLineItems' => $lineItems->count(),
            'lineItemsBreakdown' => $lineItems->map(function ($item) {
                $quantity = $item->MergedQty ?? $item->OriginalQTY ?? 0;
                $unitCost = ($item->AdjustedCost > 0)
    ? $item->AdjustedCost
    : ($item->EstimatedUnitCost ?? 0);
$lineTotal = $quantity * $unitCost;
                
                return [
                    'lineItemId' => $item->LineItemID,
                    'itemId' => $item->ItemID,
                    'itemName' => $item->item->Name ?? 'N/A',
                    'mergedQty' => $item->MergedQty,
                    'originalQty' => $item->OriginalQTY,
                    'estimatedUnitCost' => $item->EstimatedUnitCost,
                    'adjustedCost' => $item->AdjustedCost,
                    'lineTotal' => $lineTotal,
                    'branch' => $item->branch->Name ?? 'N/A',
                    'department' => $item->department->Name ?? 'N/A',
                ];
            })->toArray()
        ]);
    }

     /**
     * Log information about pending approvers
     */
    private function logPendingApprovers(): void
    {
        try {
            $pendingApprovals = $this->plan->workflowPending()
                ->with(['user', 'workflowStage'])
                ->get();

            Log::info("Pending Approvers for Plan", [
                'planId' => $this->plan->PlanID,
                'totalPendingApprovers' => $pendingApprovals->count(),
                'approvers' => $pendingApprovals->map(function ($pending) {
                    return [
                        'userId' => $pending->user->Id ?? null,
                        'userName' => $pending->user->name ?? 'Unknown User',
                        'stageId' => $pending->StageID ?? null,
                        'stageName' => $pending->workflowStage->StageName ?? 'Unknown Stage',
                        'approvalLevel' => $pending->ApprovalLevel ?? null,
                    ];
                })->toArray()
            ]);

            // Also log workflow history
         $history = $this->plan->workflowHistory()
    ->with(['creator', 'stage'])
    ->get();

Log::info("Workflow History for Plan", [
    'planId' => $this->plan->PlanID,
    'history' => $history->map(function ($history) {
        return [
            'action' => $history->Action ?? 'N/A',
            'userName' => $history->creator->name ?? 'Unknown User',
            'stageName' => $history->stage->StageName ?? 'Unknown Stage',
            'remarks' => $history->Notes ?? 'N/A',
            'createdOn' => $history->CreatedOn?->toDateTimeString() ?? 'N/A',
        ];
    })->toArray()
]);

        } catch (\Throwable $e) {
            Log::warning("Failed to log approver details", [
                'planId' => $this->plan->PlanID,
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * Approve the plan
     *
     * @param User $actor
     * @param string $remarks
     * @return bool
     * @throws ErroredException
     */
   public function approve(User $actor, string $remarks = 'Approved'): bool
    {
        Log::info("Approving Consolidated Procurement Plan", [
            'planId' => $this->plan->PlanID,
            'actorId' => $actor->Id,
        ]);

        if (!$this->workflow->canApproveModel($this->plan, $actor)) {
            throw new ErroredException('You do not have permission to approve this plan');
        }

        try {
            // Update modified by
            $this->plan->ModifiedBy = $actor->Id;
            $this->plan->save();

            // Execute approval in workflow
            $result = $this->workflow->approve(
                $this->plan,
                $actor,
                ProcurementPlanStatusEnum::Approved,
                $remarks
            );

            if (!$result) {
                throw new ErroredException('Failed to approve plan in workflow');
            }

            // Refresh to get final status from workflow
            $this->plan->refresh();

            // If fully approved, update the plan status using the mapped value
            if ($this->plan->Status === ProcurementPlanStatusEnum::Pending) {
                $approvedStatusValue = $this->workflow->getMappedStatus('Approved');
                $this->plan->Status = ProcurementPlanStatusEnum::from($approvedStatusValue);
                $this->plan->DeletedBy = $actor->Id;
                $this->plan->DeletedOn = now();
                $this->plan->save();
            }

            
            Log::info("Plan approved successfully", [
                'planId' => $this->plan->PlanID,
                'finalStatus' => $this->plan->Status->value ?? null,
            ]);
            return true;

        } catch (\Throwable $e) {
            Log::error("Failed to approve plan", [
                'planId' => $this->plan->PlanID,
                'error' => $e->getMessage(),
            ]);
            throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Reject the plan
     *
     * @param User $actor
     * @param string $remarks
     * @return bool
     * @throws ErroredException
     */
    public function reject(User $actor, string $remarks = 'Rejected'): bool
    {
        Log::info("Rejecting Consolidated Procurement Plan", [
            'planId' => $this->plan->PlanID,
            'actorId' => $actor->Id,
        ]);

        if (!$this->workflow->canApproveModel($this->plan, $actor)) {
            throw new ErroredException('You do not have permission to reject this plan');
        }

        try {
            // Update plan status to Rejected using mapped value
            $rejectedStatusValue = $this->workflow->getMappedStatus('Rejected');
            $this->plan->Status = ProcurementPlanStatusEnum::from($rejectedStatusValue);
            $this->plan->ModifiedBy = $actor->Id;
            $this->plan->save();

            // Execute rejection in workflow
            $result = $this->workflow->reject(
                $this->plan,
                $actor,
                ProcurementPlanStatusEnum::Rejected,
                $remarks
            );

            if (!$result) {
                throw new ErroredException('Failed to reject plan in workflow');
            }

        
            
            Log::info("Plan rejected successfully", [
                'planId' => $this->plan->PlanID,
                'status' => $this->plan->Status->value,
            ]);
            return true;

        } catch (\Throwable $e) {
            Log::error("Failed to reject plan", [
                'planId' => $this->plan->PlanID,
                'error' => $e->getMessage(),
            ]);
            throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Cancel/withdraw the plan from workflow
     *
     * @param User $actor
     * @param string $reason
     * @return bool
     * @throws ErroredException
     */
    public function cancel(User $actor, string $reason = 'Cancelled by submitter'): bool
    {
        Log::info("Cancelling Consolidated Procurement Plan workflow", [
            'PlanId' => $this->plan->PlanId,
            'actorId' => $actor->Id,
        ]);

        try {
            // Revert plan status to Draft
            $this->plan->Status = ProcurementPlanStatusEnum::Draft;
            $this->plan->SubmittedBy = null;
            $this->plan->SubmittedDate = null;
            $this->plan->ModifiedBy = $actor->Id;
            $this->plan->save();

            // Cancel workflow
            $result = $this->workflow->cancel($this->plan, $actor, $reason);

            if (!$result) {
                throw new ErroredException('Failed to cancel workflow');
            }

            
            // Refresh model
            $this->plan->refresh();
            
            Log::info("Plan workflow cancelled successfully", ['PlanId' => $this->plan->PlanId]);
            return true;

        } catch (\Throwable $e) {
            Log::error("Failed to cancel workflow", [
                'PlanId' => $this->plan->PlanId,
                'error' => $e->getMessage(),
            ]);
            throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Get workflow status for the plan
     *
     * @return array
     */
    public function getWorkflowStatus(): array
    {
        return $this->workflow->getStatus($this->plan);
    }

    /**
     * Get workflow history for the plan
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->workflow->historyForModel($this->plan);
    }

    /**
     * Check if a user can approve this plan
     *
     * @param User $user
     * @return bool
     */
    public function canUserApprove(User $user): bool
    {
        return $this->workflow->canApproveModel($this->plan, $user);
    }
}