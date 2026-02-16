<?php

namespace App\Services\Procurement\RFQ;

use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Procurement\RFQ;
use App\Services\Workflow\ApprovalWorkflow;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RFQWorkflowService extends ApprovalWorkflow
{
    /**
     * Constructor - Initialize with RFQ-specific settings
     */
    public function __construct()
    {
        // Pass the CodeId and the status column name to parent
        parent::__construct('RequisitionStatus', 'Status');
    }

    /**
     * Submit a model for approval (overriding parent with compatible signature)
     *
     * @param mixed $model The model instance (must be RFQ)
     * @param User $actor The user submitting
     * @param BackedEnum $pendingStatus The pending status enum value
     * @param string $remarks Optional remarks
     * @return bool
     */
    public function submit($model, User $actor, BackedEnum $pendingStatus, string $remarks = 'Submitted'): bool
    {
        // Runtime type check
        if (! $model instanceof RFQ) {
            throw new \InvalidArgumentException('Model must be an instance of RFQ');
        }

        Log::info('Submitting RFQ for approval', [
            'rfq_id' => $model->Id,
            'rfq_number' => $model->RFQNumber,
            'actor_id' => $actor->Id,
            'actor_name' => $actor->Name,
            'status' => $pendingStatus->value,
        ]);

        return parent::submit($model, $actor, $pendingStatus, $remarks);
    }

    /**
     * Convenience method with proper type hinting for RFQ submission
     */
    public function submitRFQ(RFQ $rfq, User $actor, string $remarks = 'RFQ Submitted'): bool
    {
        return $this->submit($rfq, $actor, WorkflowStatus::Submitted, $remarks);
    }

    /**
     * Approve a model (overriding parent with compatible signature)
     *
     * @param mixed $model The model instance (must be RFQ)
     * @param User $actor The user approving
     * @param BackedEnum $approvedStatus The approved status enum value
     * @param string $remarks Optional remarks
     * @param string|null $statusColumn Optional status column override
     * @return bool
     */
    public function approve($model, User $actor, BackedEnum $approvedStatus, string $remarks = 'Approved', ?string $statusColumn = null): bool
    {
        // Runtime type check
        if (! $model instanceof RFQ) {
            throw new \InvalidArgumentException('Model must be an instance of RFQ');
        }

        Log::info('Approving RFQ', [
            'rfq_id' => $model->Id,
            'rfq_number' => $model->RFQNumber,
            'current_status' => $model->Status,
            'actor_id' => $actor->Id,
            'actor_name' => $actor->Name,
            'status' => $approvedStatus->value,
        ]);

        // Use parent's approve method with the Status column explicitly
        $result = parent::approve($model, $actor, $approvedStatus, $remarks, $statusColumn ?? 'Status');

        // Refresh the model to get the updated status
        $model->refresh();

        Log::info('RFQ approval completed', [
            'rfq_id' => $model->Id,
            'new_status' => $model->Status,
            'result' => $result,
        ]);

        return $result;
    }

    /**
     * Convenience method with proper type hinting for RFQ approval
     */
    public function approveRFQ(RFQ $rfq, User $actor, string $remarks = 'RFQ Approved'): bool
    {
        return $this->approve($rfq, $actor, WorkflowStatus::APPROVED, $remarks, 'Status');
    }

    /**
     * Reject a model (overriding parent with compatible signature)
     *
     * @param mixed $model The model instance (must be RFQ)
     * @param User $actor The user rejecting
     * @param BackedEnum $rejectedStatus The rejected status enum value
     * @param string $remarks Optional remarks
     * @param string|null $statusColumn The status column name override
     * @return bool
     */
    public function reject($model, User $actor, BackedEnum $rejectedStatus, string $remarks = 'Rejected', ?string $statusColumn = null): bool
    {
        // Runtime type check
        if (! $model instanceof RFQ) {
            throw new \InvalidArgumentException('Model must be an instance of RFQ');
        }

        Log::info('Rejecting RFQ', [
            'rfq_id' => $model->Id,
            'rfq_number' => $model->RFQNumber,
            'actor_id' => $actor->Id,
            'remarks' => $remarks,
        ]);

        $result = parent::reject($model, $actor, $rejectedStatus, $remarks, $statusColumn ?? 'Status');

        // Save the rejection remarks to the model
        $model->Remarks = $remarks;
        $model->save();

        $model->refresh();

        Log::info('RFQ rejection completed', [
            'rfq_id' => $model->Id,
            'new_status' => $model->Status,
            'result' => $result,
        ]);

        return $result;
    }

    /**
     * Convenience method with proper type hinting for RFQ rejection
     */
    public function rejectRFQ(RFQ $rfq, User $actor, string $remarks = 'RFQ Rejected'): bool
    {
        return $this->reject($rfq, $actor, WorkflowStatus::REJECTED, $remarks, 'Status');
    }

    /**
     * Return an RFQ for modification
     */
    public function returnRFQ(RFQ $rfq, User $actor, string $remarks = 'RFQ Returned'): bool
    {
        Log::info('Returning RFQ for modification', [
            'rfq_id' => $rfq->Id,
            'actor_id' => $actor->Id,
        ]);

        return $this->approve($rfq, $actor, WorkflowStatus::RETURNED, $remarks, 'Status');
    }

    /**
     * Mark as under review
     */
    public function markUnderReview(RFQ $rfq, User $actor, string $remarks = 'Under Review'): bool
    {
        return $this->approve($rfq, $actor, WorkflowStatus::UnderReview, $remarks, 'Status');
    }

    /**
     * Add comments to an RFQ
     */
    public function comment(RFQ $rfq, User $actor, string $remarks): bool
    {
        return $this->approve($rfq, $actor, WorkflowStatus::COMMENTED, $remarks, 'Status');
    }

    /**
     * Get workflow history for an RFQ
     */
    public function getHistory(RFQ $rfq, int $limit = 1000)
    {
        return parent::historyForModel($rfq);
    }

    /**
     * Check if user can perform workflow action
     */
    public function canUserApprove(RFQ $rfq, User $user): bool
    {
        return parent::canApproveModel($rfq, $user);
    }

    /**
     * Get pending approvals for an RFQ
     */
    public function getPendingApprovals(RFQ $rfq): array
    {
        $sourceAlias = $rfq::getPrimaryKey() ?? $rfq->getMorphClass();

        $pending = DB::select("
            SELECT 
                p.*, 
                ws.StageName as stage_name, 
                u.Name as user_name,
                u.Email as user_email,
                wt.TypeID as workflow_type
            FROM t_WorkFlowPending p
            LEFT JOIN t_WorkFlowStages ws ON p.Stage = ws.Id
            LEFT JOIN t_Users u ON p.UserId = u.Id
            LEFT JOIN t_WorkFlowTypes wt ON ws.WorkFlowTypeId = wt.Id
            WHERE p.Source = ? AND p.SourceID = ? AND p.DeletedOn IS NULL
            ORDER BY ws.[Order] ASC
        ", [$sourceAlias, $rfq->getKey()]);

        Log::info('Pending approvals fetched', [
            'rfq_id' => $rfq->Id,
            'count' => count($pending),
            'source_alias' => $sourceAlias,
        ]);

        return $pending;
    }

    /**
     * Get available statuses for RFQs
     */
    public function getAvailableStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Core\Approval\CodeDetail::where('CodeID', 'RequisitionStatus')
            ->where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->orderBy('Order')
            ->get(['ID', 'Value', 'Description', 'Order']);
    }

    /**
     * Check if RFQ is fully approved
     */
    public function isFullyApproved(RFQ $rfq): bool
    {
        $sourceAlias = $rfq::getPrimaryKey() ?? $rfq->getMorphClass();

        $pendingCount = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM t_WorkFlowPending 
            WHERE Source = ? AND SourceID = ? AND DeletedOn IS NULL
        ", [$sourceAlias, $rfq->getKey()]);

        return $pendingCount->count === 0;
    }

    /**
     * Cancel an RFQ workflow
     * Overrides parent to maintain compatibility while providing type safety
     */
    public function cancel($model, User $actor, string $reason = 'Cancelled'): bool
    {
        if (! $model instanceof RFQ) {
            throw new \InvalidArgumentException('Model must be an instance of RFQ');
        }

        return parent::cancel($model, $actor, $reason);
    }

    /**
     * Convenience method with proper type hinting
     */
    public function cancelRFQ(RFQ $rfq, User $actor, string $reason = 'RFQ Cancelled'): bool
    {
        return $this->cancel($rfq, $actor, $reason);
    }

    /**
     * Get current workflow status
     * Overrides parent to maintain compatibility
     */
    public function getStatus($model): array
    {
        if (! $model instanceof RFQ) {
            throw new \InvalidArgumentException('Model must be an instance of RFQ');
        }

        return parent::getStatus($model);
    }

    /**
     * Convenience method with proper type hinting
     */
    public function getRFQWorkflowStatus(RFQ $rfq): array
    {
        return $this->getStatus($rfq);
    }
}
