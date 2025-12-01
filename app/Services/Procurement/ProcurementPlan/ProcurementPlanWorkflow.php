<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Database\Eloquent\Collection;

class ProcurementPlanWorkflow extends ApprovalWorkflowService
{
    // Match CodeID used for Procurement Plan in t_CodeDetails
    public const CODE_ID = 'ProcurementPlanStatus';

    /**
     * Submit a Procurement Plan for approval
     * 
     * @param ConsolidatedProcurementPlan $plan
     * @param User $actor
     * @param string $remarks
     * @return bool
     * @throws ErroredException
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

    /**
     * Get workflow history for Procurement Plans
     * 
     * @param int $limit
     * @return Collection
     * @throws ErroredException
     */
    public function history(int $limit = 1000): Collection
    {
        return $this->historyData(ConsolidatedProcurementPlan::getPrimaryKey(), $limit);
    }

    /**
     * Get workflow history for a specific Procurement Plan
     * 
     * @param ConsolidatedProcurementPlan $plan
     * @return Collection
     */
    public function historyForPlan(ConsolidatedProcurementPlan $plan): Collection
    {
        return $plan->workflowHistory()
            ->with(['creator', 'status', 'stage'])
            ->get();
    }

    /**
     * Check if a user can approve a specific Procurement Plan
     * 
     * @param ConsolidatedProcurementPlan $plan
     * @param User $user
     * @return bool
     */
    public function canApprovePlan(ConsolidatedProcurementPlan $plan, User $user): bool
    {
        return parent::canApprove(
            ConsolidatedProcurementPlan::getPrimaryKey(),
            $plan->getKey(),
            $user
        );
    }
}
