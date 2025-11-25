<?php

namespace App\Services\Procurement\Requisition;
use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Core\Approval;
use App\Models\Procurement\Requisitions;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Support\Facades\DB;



class RequisitionWorkFlowService extends ApprovalWorkflowService
{
    protected string $codeId = 'RequisitionStatus'; // Purchase Requisition Code ID

  

    /**
     * Submit a purchase requisition for approval
     */
    public function submit(Requisitions $requisition, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::Submitted, $this->codeId);
        return $this->submittedAction($actor, $status, $requisition->getMorphClass(), $requisition->getKey(), $remarks);
    }

    /**
     * Approve a purchase requisition
     */
    public function approve(Requisitions $requisition, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::APPROVED, $this->codeId);
        return $this->approveAction($actor, $status, $requisition->getMorphClass(), $requisition->getKey(), $remarks);
    }

    /**
     * Reject a purchase requisition
     */
    public function reject(Requisitions $requisition, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::REJECTED, $this->codeId);
        return $this->rejectAction($actor, $status, $requisition->getMorphClass(), $requisition->getKey(), $remarks);
    }

    /**
     * Return a purchase requisition for modification
     */
    public function return(Requisitions $requisition, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::RETURNED, $this->codeId);
        return $this->approveAction($actor, $status, $requisition->getMorphClass(), $requisition->getKey(), $remarks);
    }

    /**
     * Mark as under review
     */
    public function markUnderReview(Requisitions $requisition, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::UnderReview, $this->codeId);
        return $this->approveAction($actor, $status, $requisition->getMorphClass(), $requisition->getKey(), $remarks);
    }

    /**
     * Add comments to a requisition
     */
    public function comment(Requisitions $requisition, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::COMMENTED, $this->codeId);
        return $this->approveAction($actor, $status, $requisition->getMorphClass(), $requisition->getKey(), $remarks);
    }

    /**
     * Get workflow history for a purchase requisition
     */
    public function getHistory(Requisitions $requisition, int $limit = 1000)
    {
        return $this->historyData($requisition->getMorphClass(), $limit)
            ->where('SourceID', $requisition->getKey())
            ->values();
    }

    /**
     * Check if user can perform workflow action
     */
    public function canUserApprove(Requisitions $requisition, User $user): bool
    {
        // Check if user has pending workflow task
        $pending = \DB::select("
            SELECT COUNT(*) as count 
            FROM t_WorkFlowPending 
            WHERE Source = ? AND SourceID = ? AND UserId = ? AND DeletedOn IS NULL
        ", [$requisition->getTable(), $requisition->getKey(), $user->Id]);

        return $pending[0]->count > 0;
    }

    /**
     * Get pending approvals for a purchase requisition
     */
    public function getPendingApprovals(Requisitions $requisition): array
    {
        return \DB::select("
            SELECT 
                p.*, 
                ws.Name as stage_name, 
                u.Name as user_name,
                u.Email as user_email,
                wt.TypeID as workflow_type
            FROM t_WorkFlowPending p
            JOIN t_WorkFlowStages ws ON p.Stage = ws.Id
            JOIN t_Users u ON p.UserId = u.Id
            JOIN t_WorkFlowTypes wt ON ws.WorkFlowTypeId = wt.Id
            WHERE p.Source = ? AND p.SourceID = ? AND p.DeletedOn IS NULL
            ORDER BY ws.[Order] ASC
        ", [$requisition->getTable(), $requisition->getKey()]);
    }

    /**
     * Get available statuses for requisitions
     */
    public function getAvailableStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Core\CodeDetail::where('CodeID', $this->codeId)
            ->orderBy('Order')
            ->get(['ID', 'value', 'Description', 'Order']);
    }

    /**
     * Check if requisition is fully approved
     */
    public function isFullyApproved(Requisitions $requisition): bool
    {
        $pendingCount = DB::select("
            SELECT COUNT(*) as count 
            FROM t_WorkFlowPending 
            WHERE Source = ? AND SourceID = ? AND DeletedOn IS NULL
        ", [$requisition->getTable(), $requisition->getKey()]);

        return $pendingCount[0]->count === 0;
    }
}

