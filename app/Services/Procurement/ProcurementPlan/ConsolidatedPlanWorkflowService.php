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



        $this->workflow = new ProcurementPlanWorkflow();
    }

    /**
     * Submit plan for approval
     */
    public function submitForApproval(User $actor, string $remarks = 'Submitted for approval'): bool
    {


        if (is_null($this->plan->PlanID)) {
            throw new ErroredException('Invalid plan: PlanID is missing');
        }

        $allowedStatuses = [
            ProcurementPlanStatusEnum::Draft,
            ProcurementPlanStatusEnum::Rejected,
        ];

        if (! in_array($this->plan->Status, $allowedStatuses)) {
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



            $this->logLineItemDetails();

            // Update plan status to Pending
            $pendingStatusValue = $this->workflow->getMappedStatus('Pending');
            $this->plan->Status = ProcurementPlanStatusEnum::from($pendingStatusValue);
            $this->plan->SubmittedBy = $actor->Id;
            $this->plan->SubmittedDate = now();
            $this->plan->ModifiedBy = $actor->Id;
            $this->plan->save();



            // Submit to workflow
            $result = $this->workflow->submit(
                $this->plan,
                $actor,
                ProcurementPlanStatusEnum::Pending,
                $remarks
            );

            if (! $result) {
                // throw new ErroredException('Failed to submit plan to workflow');
            }

            $this->logPendingApprovers();

            DB::commit();


            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            // Log::error("Failed to submit plan for approval", [
            //     'planId' => $this->plan->PlanID,
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            // throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Approve the plan
     */
    public function approve(User $actor, string $remarks = 'Approved'): bool
    {


        //     throw new ErroredException('You do not have permission to approve this plan');
        // }

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

            if (! $result) {
                throw new ErroredException('Failed to approve plan in workflow');
            }

            DB::commit();

            // Refresh to get updated status from workflow
            $this->plan->refresh();



            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            // Log::error("Failed to approve plan", [
            //     'planId' => $this->plan->PlanID,
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            // throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Reject the plan
     */
    public function reject(User $actor, string $remarks = 'Rejected'): bool
    {


        if (! $this->workflow->canApproveModel($this->plan, $actor)) {
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

            if (! $result) {
                throw new ErroredException('Failed to reject plan in workflow');
            }

            DB::commit();



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

            if (! $result) {
                throw new ErroredException('Failed to cancel workflow');
            }

            DB::commit();

            $this->plan->refresh();


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



            $history = $this->plan->workflowHistory()
                ->with(['creator', 'stage'])
                ->get();
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
