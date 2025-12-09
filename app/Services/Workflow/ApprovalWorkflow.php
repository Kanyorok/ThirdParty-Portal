<?php

namespace App\Services\Workflow;  

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;  
use Illuminate\Support\Facades\Log;

class ApprovalWorkflow extends ApprovalWorkflowService
{
    private string $codeId;
    private string $statusColumn;

    /**
     * Constructor to configure the workflow for a specific module.
     * 
     * @param string $codeId The CodeID for the module 
     * @param string $statusColumn The dynamic status column to be passed
     */
    public function __construct(string $codeId, string $statusColumn = 'Status')
    {
        $this->codeId = $codeId;
        $this->statusColumn = $statusColumn;
        
        Log::info("ApprovalWorkflow initialized", [
            'codeId' => $codeId,
            'statusColumn' => $statusColumn,
            'class' => static::class
        ]);
    }

    /**
     * Submit a model for approval (generic version).
     * 
     * @param mixed $model The model instance (e.g., DepartmentNeed, Tender).
     * @param User $actor The user submitting.
     * @param BackedEnum $pendingStatus The pending status enum value.
     * @param string $remarks Optional remarks.
     * @return bool
     * @throws ErroredException
     */
    public function submit($model, User $actor, BackedEnum $pendingStatus, string $remarks = 'Submitted'): bool
    {
        try {
            $status = self::codeDetail($pendingStatus, $this->codeId);
            
            Log::info("Submitting for approval", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'actor_id' => $actor->Id,
                'pending_status' => $pendingStatus->value,
                'status_detail' => $status,
                'remarks' => $remarks
            ]);
            
            // Use the model's morph alias
            $result = $this->submittedAction(
                $actor, 
                $status, 
                $model, // model instance 
                $model::getPrimaryKey() ?? $model->getMorphClass(), // Source alias
                $model->getKey(), // sourceID
                $remarks
            );
            
            Log::info("Submission result", [
                'success' => $result,
                'model_class' => get_class($model),
                'model_id' => $model->getKey()
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("Failed to submit for approval", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Approve a model 
     * 
     * @param mixed $model The model instance.
     * @param User $actor The user approving.
     * @param BackedEnum $approvedStatus The approved status enum value.
     * @param string $remarks Optional remarks.
     * @param string|null $statusColumn Optional status column override
     * @return bool
     * @throws ErroredException
     */
    public function approve($model, User $actor, BackedEnum $approvedStatus, string $remarks = 'Approved', ?string $statusColumn = null): bool
    {
        try {
            $status = self::codeDetail($approvedStatus, $this->codeId);
            
            // Use provided column or fall back to instance default
            $columnToUse = $statusColumn ?? $this->statusColumn;
            
            Log::info("Approving with status column", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'actor_id' => $actor->Id,
                'approved_status' => $approvedStatus->value,
                'status_detail' => $status,
                'provided_column' => $statusColumn,
                'instance_column' => $this->statusColumn,
                'column_to_use' => $columnToUse,
                'remarks' => $remarks
            ]);

            // Capture the result from approveAction
            $result = $this->approveAction(
                $actor,
                $status,
                $model::getPrimaryKey(),
                $model->getKey(),
                $remarks,
                $columnToUse // Use the determined column
            );

            Log::info("Approval result", [
                'success' => isset($result['success']) && $result['success'] === true,
                'result' => $result,
                'model_class' => get_class($model),
                'model_id' => $model->getKey()
            ]);

            // Return a boolean indicating success
            return isset($result['success']) && $result['success'] === true;
            
        } catch (\Exception $e) {
            Log::error("Failed to approve", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Reject a model (generic version).
     * 
     * @param mixed $model The model instance.
     * @param User $actor The user rejecting.
     * @param BackedEnum $rejectedStatus The rejected status enum value.
     * @param string $remarks Optional remarks.
     * @param string|null $statusColumn The status column name override
     * @return bool
     * @throws ErroredException
     */
    public function reject($model, User $actor, BackedEnum $rejectedStatus, string $remarks = 'Rejected', ?string $statusColumn = null): bool
    {
        try {
            $status = self::codeDetail($rejectedStatus, $this->codeId);
            
            // Use provided column or fall back to instance default
            $columnToUse = $statusColumn ?? $this->statusColumn;
            
            Log::info("Rejecting with status column", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'actor_id' => $actor->Id,
                'rejected_status' => $rejectedStatus->value,
                'status_detail' => $status,
                'provided_column' => $statusColumn,
                'instance_column' => $this->statusColumn,
                'column_to_use' => $columnToUse,
                'remarks' => $remarks
            ]);
            
            $result = $this->rejectAction(
                $actor, 
                $status, 
                $model::getPrimaryKey(), 
                $model->getKey(), 
                $remarks, 
                $columnToUse // Use the determined column
            );
            
            Log::info("Rejection result", [
                'success' => $result,
                'model_class' => get_class($model),
                'model_id' => $model->getKey()
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("Failed to reject", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get workflow history for a module.
     * 
     * @param string $morphAlias The morph alias for the model.
     * @param int $limit
     * @return Collection
     * @throws ErroredException
     */
    public function history(string $morphAlias, int $limit = 1000): Collection
    {
        return $this->historyData($morphAlias, $limit);
    }

    /**
     * Get workflow history for a specific model instance.
     * 
     * @param mixed $model The model instance (must have a workflowHistory() relationship).
     * @return Collection
     */
    public function historyForModel($model): Collection
    {
        Log::info("Fetching workflow history", [
            'model_class' => get_class($model),
            'model_id' => $model->getKey()
        ]);
        
        return $model->workflowHistory()
            ->with(['creator', 'status', 'stage'])
            ->orderBy('CreatedOn', 'desc')
            ->get();
    }

    /**
     * Check if a user can approve a specific model.
     * 
     * @param mixed $model The model instance.
     * @param User $user The user to check.
     * @return bool
     */
    public function canApproveModel($model, User $user): bool
    {
        try {
            $canApprove = parent::canApprove(
                get_class($model),
                $model->getKey(), 
                $user
            );
            
            Log::info("Permission check result", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'user_id' => $user->Id,
                'can_approve' => $canApprove
            ]);
            
            return $canApprove;
            
        } catch (\Exception $e) {
            Log::error("Permission check failed", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'user_id' => $user->Id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cancel a workflow
     */
    public function cancel($model, User $actor, string $reason = 'Cancelled'): bool
    {
        try {
            Log::info("Cancelling workflow", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'actor_id' => $actor->Id,
                'reason' => $reason
            ]);
            
            return $this->cancelWorkflow($actor, $model::getPrimaryKey(), $model->getKey(), $reason);
            
        } catch (\Exception $e) {
            Log::error("Failed to cancel workflow", [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get workflow status
     */
    public function getStatus($model): array
    {
        return $this->getWorkflowStatus($model::getPrimaryKey(), $model->getKey());
    }

    /**
     * Get the status column being used by this workflow instance
     */
    public function getStatusColumn(): string
    {
        return $this->statusColumn;
    }
}