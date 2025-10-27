<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeed;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Database\Eloquent\Collection;

class DepartmentNeedsWorkflow extends ApprovalWorkflowService
{
    // Match existing CodeID used for Department Needs in t_CodeDetails
    public const CODE_ID = 'DepartmentNeedsStatus';

    /**
     * Submit a Department Need for approval
     * 
     * @param DepartmentNeed $need
     * @param User $actor
     * @param string $remarks
     * @return bool
     * @throws ErroredException
     */
    public function submit(DepartmentNeed $need, User $actor, string $remarks = 'Submitted'): bool
    {
        // On submit, DepartmentNeeds use Pending status code
        $status = self::codeDetail(DepartmentNeedsEnum::Pending, self::CODE_ID);
        
        // Use the morph map alias for consistency across the system
        return $this->submittedAction(
            $actor, 
            $status, 
            $need,
            DepartmentNeed::getPrimaryKey(), // Use the morph map alias
            $need->getKey(), 
            $remarks
        );
    }

    /**
     * Approve a Department Need
     * 
     * @param DepartmentNeed $need
     * @param User $actor
     * @param string $remarks
     * @param string $statusColumn
     * @return bool
     * @throws ErroredException
     */
    public function approve(DepartmentNeed $need, User $actor, string $remarks = 'Approved', string $statusColumn = 'Status'): bool
    {
        $status = self::codeDetail(DepartmentNeedsEnum::Approved, self::CODE_ID);
        
        // Use the morph map alias instead of table name
        return $this->approveAction(
            $actor, 
            $status, 
            DepartmentNeed::getPrimaryKey(), // Consistent with submit()
            $need->getKey(), 
            $remarks, 
            $statusColumn
        );
    }

    /**
     * Reject a Department Need
     * 
     * @param DepartmentNeed $need
     * @param User $actor
     * @param string $remarks
     * @param string $statusColumn
     * @return bool
     * @throws ErroredException
     */
    public function reject(DepartmentNeed $need, User $actor, string $remarks = 'Rejected', string $statusColumn = 'Status'): bool
    {
        $status = self::codeDetail(DepartmentNeedsEnum::Rejected, self::CODE_ID);
        
        // Use the morph map alias instead of table name
        return $this->rejectAction(
            $actor, 
            $status, 
            DepartmentNeed::getPrimaryKey(), // Consistent with submit()
            $need->getKey(), 
            $remarks, 
            $statusColumn
        );
    }

    /**
     * Get workflow history for Department Needs
     * 
     * @param int $limit
     * @return Collection
     * @throws ErroredException
     */
    public function history(int $limit = 1000): Collection
    {
        return $this->historyData(DepartmentNeed::getPrimaryKey(), $limit);
    }

    /**
     * Get workflow history for a specific Department Need
     * 
     * @param DepartmentNeed $need
     * @return Collection
     */
    public function historyForNeed(DepartmentNeed $need): Collection
    {
        return $need->workflowHistory()
            ->with(['creator', 'status', 'stage'])
            ->orderBy('CreatedOn', 'desc')
            ->get();
    }

    /**
     * Check if a user can approve a specific Department Need (public wrapper for maker-checker)
     * 
     * @param DepartmentNeed $need
     * @param User $user
     * @return bool
     */
    public function canApproveNeed(DepartmentNeed $need, User $user): bool
    {
        // Call the parent's protected canApprove method with resolved table and ID
        $table = $need->getTable();
        return parent::canApprove($table, $need->getKey(), $user);
    }
}