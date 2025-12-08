<?php

namespace App\Services\Procurement\RFQ;

use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Procurement\RFQ;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Support\Facades\DB;

class RFQWorkflowService extends ApprovalWorkflowService
{
    protected string $codeId = 'RequisitionStatus'; // Using RequisitionStatus codes (Ap, pe, Re) for consistency

    /**
     * Submit an RFQ for approval
     */
    public function submit(RFQ $rfq, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::Submitted, $this->codeId);
        return $this->submittedAction(
            $actor,
            $status,
            $rfq,
            $rfq->getMorphClass(),
            $rfq->getKey(),
            $remarks
        );
    }

    /**
     * Approve an RFQ
     */
    public function approve(RFQ $rfq, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::APPROVED, $this->codeId);
        // Using 'Status' column for RFQ as per current schema, but values will be 'Ap' etc.
        return $this->approveAction($actor, $status, $rfq->getMorphClass(), $rfq->getKey(), $remarks, 'Status');
    }

    /**
     * Reject an RFQ
     */
    public function reject(RFQ $rfq, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::REJECTED, $this->codeId);
        return $this->rejectAction($actor, $status, $rfq->getMorphClass(), $rfq->getKey(), $remarks, 'Status');
    }

    /**
     * Return an RFQ for modification
     */
    public function return(RFQ $rfq, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::RETURNED, $this->codeId);
        return $this->approveAction($actor, $status, $rfq->getMorphClass(), $rfq->getKey(), $remarks, 'Status');
    }

    /**
     * Mark as under review
     */
    public function markUnderReview(RFQ $rfq, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::UnderReview, $this->codeId);
        return $this->approveAction($actor, $status, $rfq->getMorphClass(), $rfq->getKey(), $remarks, 'Status');
    }

    /**
     * Add comments to an RFQ
     */
    public function comment(RFQ $rfq, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(WorkflowStatus::COMMENTED, $this->codeId);
        return $this->approveAction($actor, $status, $rfq->getMorphClass(), $rfq->getKey(), $remarks, 'Status');
    }

    /**
     * Get workflow history for an RFQ
     */
    public function getHistory(RFQ $rfq, int $limit = 1000)
    {
        return $this->historyData($rfq->getMorphClass(), $limit)
            ->where('SourceID', $rfq->getKey())
            ->values();
    }

    /**
     * Check if user can perform workflow action
     */
    public function canUserApprove(RFQ $rfq, User $user): bool
    {
        // Check if user has pending workflow task
        $pending = DB::select("
            SELECT COUNT(*) as count 
            FROM t_WorkFlowPending 
            WHERE Source = ? AND SourceID = ? AND UserId = ? AND DeletedOn IS NULL
        ", [$rfq->getTable(), $rfq->getKey(), $user->Id]);

        return $pending[0]->count > 0;
    }

    /**
     * Get pending approvals for an RFQ
     */
    public function getPendingApprovals(RFQ $rfq): array
    {
        return DB::select("
            SELECT 
                p.*, 
                ws.StageName as stage_name, 
                u.Name as user_name,
                u.Email as user_email,
                wt.TypeID as workflow_type
            FROM t_WorkFlowPending p
            JOIN t_WorkFlowStages ws ON p.Stage = ws.Id
            JOIN t_Users u ON p.UserId = u.Id
            JOIN t_WorkFlowTypes wt ON ws.WorkFlowTypeId = wt.Id
            WHERE p.Source = ? AND p.SourceID = ? AND p.DeletedOn IS NULL
            ORDER BY ws.[Order] ASC
        ", [$rfq->getTable(), $rfq->getKey()]);
    }

    /**
     * Get available statuses for RFQs
     */
    public function getAvailableStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Core\CodeDetail::where('CodeID', $this->codeId)
            ->orderBy('Order')
            ->get(['ID', 'value', 'Description', 'Order']);
    }

    /**
     * Check if RFQ is fully approved
     */
    public function isFullyApproved(RFQ $rfq): bool
    {
        $pendingCount = DB::select("
            SELECT COUNT(*) as count 
            FROM t_WorkFlowPending 
            WHERE Source = ? AND SourceID = ? AND DeletedOn IS NULL
        ", [$rfq->getTable(), $rfq->getKey()]);

        return $pendingCount[0]->count === 0;
    }
}
