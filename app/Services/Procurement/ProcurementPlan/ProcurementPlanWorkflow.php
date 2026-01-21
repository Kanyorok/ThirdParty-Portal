<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Services\Core\ApprovalWorkflowService;

class ProcurementPlanWorkflow extends ApprovalWorkflowService
{
    public const CODE_ID = 'ProcurementPlanStatus';

    /**
     * Submit the plan for approval.
     */
    public function submit(ConsolidatedProcurementPlan $plan, User $actor, string $remarks = 'Submitted'): bool
    {
        // On submit, use Pending status (P) as per workflow requirement
        $status = self::codeDetail(ProcurementPlanStatusEnum::Pending, self::CODE_ID);

        return $this->submittedAction(
            $actor,
            $status,
            $plan,
            ConsolidatedProcurementPlan::getPrimaryKey(),
            $plan->getKey(),
            $remarks
        );
    }

    /**
     * Approve the plan.
     */
    public function approve(ConsolidatedProcurementPlan $plan, User $actor, ProcurementPlanStatusEnum $targetStatus, string $remarks = 'Approved', string $statusColumn = 'Status'): bool
    {
        $status = self::codeDetail($targetStatus, self::CODE_ID);

        return $this->approveAction(
            $actor,
            $status,
            ConsolidatedProcurementPlan::getPrimaryKey(),
            $plan->getKey(),
            $remarks,
            $statusColumn
        );
    }

    /**
     * Reject the plan.
     */
    public function reject(ConsolidatedProcurementPlan $plan, User $actor, ProcurementPlanStatusEnum $targetStatus, string $remarks = 'Rejected', string $statusColumn = 'Status'): bool
    {
        $status = self::codeDetail($targetStatus, self::CODE_ID);

        return $this->rejectAction(
            $actor,
            $status,
            ConsolidatedProcurementPlan::getPrimaryKey(),
            $plan->getKey(),
            $remarks,
            $statusColumn
        );
    }

    public function history(ConsolidatedProcurementPlan $plan)
    {
        return $this->historyData(ConsolidatedProcurementPlan::getPrimaryKey(), 1000); // Assuming limit 1000
    }

    public function canApprovePlan(ConsolidatedProcurementPlan $plan, User $user): bool
    {
        return parent::canApprove(ConsolidatedProcurementPlan::getPrimaryKey(), $plan->getKey(), $user);
    }
}
