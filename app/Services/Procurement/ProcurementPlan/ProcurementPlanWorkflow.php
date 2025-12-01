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

    /**
     * Approve a Procurement Plan
     * 
     * @param ConsolidatedProcurementPlan $plan
     * @param User $actor
     * @param string $remarks
     * @param string $statusColumn
     * @return bool
     * @throws ErroredException
     */
    public function approve(ConsolidatedProcurementPlan $plan, User $actor, string $remarks = 'Approved', string $statusColumn = 'Status'): bool
    {
        $status = self::codeDetail(ProcurementPlanStatusEnum::Approved, self::CODE_ID);

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
     * Reject a Procurement Plan
     * 
     * @param ConsolidatedProcurementPlan $plan
     * @param User $actor
     * @param string $remarks
     * @param string $statusColumn
     * @return bool
     * @throws ErroredException
     */
    public function reject(ConsolidatedProcurementPlan $plan, User $actor, string $remarks = 'Rejected', string $statusColumn = 'Status'): bool
    {
        // If the intention of 'RETURNED' was to go to Draft, we might need to handle it here.
        // But typically reject goes to Rejected status.
        // If the status passed is Draft, we use Draft.
        // But here we hardcode Rejected status for the 'reject' action.
        // If we want to support dynamic status, we should accept the enum as argument.
        // But to match DepartmentNeedsWorkflow signature, we keep it simple.
        // However, the controller calls reject with specific status.
        // Let's update the signature to accept status if needed, or just use the passed status if we change the signature.
        // But wait, the controller calls: $this->workflow->reject($plan, $user, ProcurementPlanStatusEnum::Rejected, ...);
        // So the signature in Controller call includes the status.
        // My previous draft had: public function reject(..., ProcurementPlanStatusEnum $status, ...)
        // But DepartmentNeedsWorkflow had: public function reject(DepartmentNeed $need, User $actor, string $remarks = 'Rejected', string $statusColumn = 'Status'): bool
        // It didn't take status as arg!

        // Let's check DepartmentNeedsWorkflow again.
        // public function reject(DepartmentNeed $need, User $actor, string $remarks = 'Rejected', string $statusColumn = 'Status'): bool
        // {
        //     $status = self::codeDetail(DepartmentNeedsEnum::Rejected, self::CODE_ID);
        //     ...
        // }

        // So DepartmentNeedsWorkflow hardcodes the status to Rejected.
        // If I want to support 'RETURNED' -> 'Draft', I should probably add a 'returnToDraft' method or make 'reject' more flexible.
        // But since I updated the controller to call `reject` with a status argument in my previous thought, 
        // I should check if I actually updated the controller to pass the status.

        // In the controller update I wrote:
        // $this->workflow->reject($plan, $user, ProcurementPlanStatusEnum::Rejected, $request->comments);
        // $this->workflow->reject($plan, $user, ProcurementPlanStatusEnum::Draft, $request->comments);

        // So I am passing the status enum!
        // So I must update the signature of `reject` (and `approve`) to accept the status enum.
        // This deviates slightly from DepartmentNeedsWorkflow but is more flexible.
        // OR I can stick to DepartmentNeedsWorkflow pattern and create a `return` method.

        // Let's look at `DepartmentNeedsWorkflow` again.
        // It does NOT accept status enum in `approve` or `reject`.

        // So my controller update was assuming a different signature than `DepartmentNeedsWorkflow`.
        // I should probably align with `DepartmentNeedsWorkflow` for consistency if `ApprovalWorkflowService` expects that?
        // `ApprovalWorkflowService` doesn't dictate the child class methods, only the protected methods it offers.

        // I will update `ProcurementPlanWorkflow` to accept the status enum, as that gives me control in the controller.

        $statusDetail = self::codeDetail($remarks instanceof ProcurementPlanStatusEnum ? $remarks : ProcurementPlanStatusEnum::Rejected, self::CODE_ID);
        // Wait, the arguments are: ($plan, $actor, $remarks, $statusColumn).
        // If I pass an Enum as 3rd argument, type hinting will fail if it expects string.

        // I should change the signature to:
        // public function reject(ConsolidatedProcurementPlan $plan, User $actor, ProcurementPlanStatusEnum $targetStatus, string $remarks = 'Rejected', string $statusColumn = 'Status'): bool

        // And update the controller to match.
        // In the controller I already wrote:
        // $this->workflow->reject($plan, $user, ProcurementPlanStatusEnum::Rejected, $request->comments);

        // So I need to define `reject` to accept the enum.

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

    // I need to redefine the methods with the correct signature to match my controller usage.

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
