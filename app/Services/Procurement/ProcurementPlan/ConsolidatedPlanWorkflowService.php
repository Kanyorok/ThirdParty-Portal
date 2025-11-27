<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
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
        
        $this->workflow = new ProcurementPlanWorkflow();
    }

    /**
     * Submit plan for approval
     */
    public function submitForApproval(User $actor, string $remarks = 'Submitted for approval'): bool
    {
        Log::info("Submitting Consolidated Procurement Plan for approval", [
            'planId' => $this->plan->PlanID,
            'actorId' => $actor->Id,
            'currentStatus' => $this->plan->Status->value ?? null,
        ]);

        if (is_null($this->plan->PlanID)) {
            throw new ErroredException('Invalid plan: PlanID is missing');
        }

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

        if ($this->plan->lineItems()->count() === 0) {
            throw new ErroredException('Cannot submit a plan without line items');
        }

        try {
            DB::beginTransaction();

            // If resubmitting, clean up old workflow
            if ($this->plan->Status === ProcurementPlanStatusEnum::Rejected) {
                DB::table('t_WorkFlowPending')
                    ->where('Source', $this->plan->getTable())
                    ->where('SourceID', (string)$this->plan->PlanID)
                    ->whereNull('DeletedOn')
                    ->update([
                        'DeletedOn' => now(),
                        'DeletedBy' => $actor->Id,
                    ]);
            }

            // Calculate total amount
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

            // Update plan status to Pending
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

            // Submit to workflow
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
     * Approve the plan
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
            DB::beginTransaction();

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

            DB::commit();

            // Refresh to get updated status from workflow
            $this->plan->refresh();

            Log::info("Plan approved successfully", [
                'planId' => $this->plan->PlanID,
                'finalStatus' => $this->plan->Status->value ?? null,
            ]);
            
            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to approve plan", [
                'planId' => $this->plan->PlanID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Reject the plan
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
            DB::beginTransaction();

            // Update plan status to Rejected
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

            DB::commit();
            
            Log::info("Plan rejected successfully", [
                'planId' => $this->plan->PlanID,
                'status' => $this->plan->Status->value,
            ]);
            
            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to reject plan", [
                'planId' => $this->plan->PlanID,
                'error' => $e->getMessage(),
            ]);
            throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Cancel/withdraw the plan from workflow
     */
    public function cancel(User $actor, string $reason = 'Cancelled by submitter'): bool
    {
        Log::info("Cancelling Consolidated Procurement Plan workflow", [
            'planId' => $this->plan->PlanID,
            'actorId' => $actor->Id,
        ]);

        try {
            DB::beginTransaction();

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

            DB::commit();
            
            $this->plan->refresh();
            
            Log::info("Plan workflow cancelled successfully", ['planId' => $this->plan->PlanID]);
            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to cancel workflow", [
                'planId' => $this->plan->PlanID,
                'error' => $e->getMessage(),
            ]);
            throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Log detailed line item information
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
                        'userName' => $pending->user->Name ?? 'Unknown User',
                        'stageId' => $pending->Stage ?? null,
                        'stageName' => $pending->workflowStage->StageName ?? 'Unknown Stage',
                    ];
                })->toArray()
            ]);

            $history = $this->plan->workflowHistory()
                ->with(['creator', 'stage'])
                ->get();

            Log::info("Workflow History for Plan", [
                'planId' => $this->plan->PlanID,
                'history' => $history->map(function ($h) {
                    return [
                        'statusId' => $h->StatusId ?? 'N/A',
                        'userName' => $h->creator->Name ?? 'Unknown User',
                        'stageName' => $h->stage->StageName ?? 'Unknown Stage',
                        'remarks' => $h->Notes ?? 'N/A',
                        'createdOn' => $h->CreatedOn?->toDateTimeString() ?? 'N/A',
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

    public function getWorkflowStatus(): array
    {
        return $this->workflow->getStatus($this->plan);
    }

    public function getHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->workflow->historyForModel($this->plan);
    }

    public function canUserApprove(User $user): bool
    {
        return $this->workflow->canApproveModel($this->plan, $user);
    }
}