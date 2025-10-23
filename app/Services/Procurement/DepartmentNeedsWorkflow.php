<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeed;
use App\Services\Core\ApprovalWorkflowService;

class DepartmentNeedsWorkflow extends ApprovalWorkflowService
{
    // Match existing CodeID used for Department Needs in t_CodeDetails
    public const CODE_ID = 'DepartmentNeedsStatus';

    /**
     * Submit a Department Need for approval
     */
    public function submit(DepartmentNeed $need, User $actor, string $remarks = 'Submitted'): bool
    {
        // On submit, DepartmentNeeds use Pending status code
        $status = self::codeDetail(DepartmentNeedsEnum::Pending, self::CODE_ID);
        return $this->submittedAction($actor, $status, $need,$need::getPrimaryKey() ,$need->getKey(), $remarks);
    }

    /**
     * Approve a Department Need
     */
    public function approve(DepartmentNeed $need, User $actor, string $remarks = 'Approved', string $statusColumn = 'Status'): bool
    {
        $status = self::codeDetail(DepartmentNeedsEnum::Approved, self::CODE_ID);
        return $this->approveAction($actor, $status, $need->getTable(), $need->getKey(), $remarks, $statusColumn);
    }

    /**
     * Reject a Department Need
     */
    public function reject(DepartmentNeed $need, User $actor, string $remarks = 'Rejected', string $statusColumn = 'Status'): bool
    {
        $status = self::codeDetail(DepartmentNeedsEnum::Rejected, self::CODE_ID);
        return $this->rejectAction($actor, $status, $need->getTable(), $need->getKey(), $remarks, $statusColumn);
    }
}
