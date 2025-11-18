<?php

namespace App\Services\Workflow;  

use App\Enums\Procurement\DepartmentNeedsEnum;  
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeed;  
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;  
use Illuminate\Support\Facades\Log;
class ApprovalWorkflow extends ApprovalWorkflowService
{
    private string $codeId;

    /**
     * Constructor to configure the workflow for a specific module.
     * 
     * @param string $codeId The CodeID for the module 
     */
    public function __construct(string $codeId)
    {
        $this->codeId = $codeId;
    }

    /**
     * Submit a model for approval (generic version).
     * 
     * @param mixed $model The model instance (e.g., DepartmentNeed).
     * @param User $actor The user submitting.
     * @param BackedEnum $pendingStatus The pending status enum value.
     * @param string $remarks Optional remarks.
     * @return bool
     * @throws ErroredException
     */
    public function submit($model, User $actor, BackedEnum $pendingStatus, string $remarks = 'Submitted'): bool
    {
        $status = self::codeDetail($pendingStatus, $this->codeId);
        
        // Use the model's morph alias
        return $this->submittedAction(
            $actor, 
            $status, 
            $model, //model instance 
            $model::getPrimaryKey() ?? $model->getMorphClass(),  // Source alis
            $model->getKey(), //sourceID
            $remarks
        );
    }

    /**
     * Approve a model 
     * 
     * @param mixed $model The model instance.
     * @param User $actor The user approving.
     * @param BackedEnum $approvedStatus The approved status enum value.
     * @param string $remarks Optional remarks.
     * @param string $statusColumn 
     * @return bool
     * @throws ErroredException
     */
    public function approve($model, User $actor, BackedEnum $approvedStatus, string $remarks = 'Approved', string $statusColumn = 'Status'): bool
{
    $status = self::codeDetail($approvedStatus, $this->codeId);

    // Capture the result from approveAction
    $result = $this->approveAction(
        $actor,
        $status,
        $model::getPrimaryKey(),
        $model->getKey(),
        $remarks,
        $statusColumn
    );

    // Return a boolean indicating success
    return isset($result['success']) && $result['success'] === true;
}


    /**
     * Reject a model (generic version).
     * 
     * @param mixed $model The model instance.
     * @param User $actor The user rejecting.
     * @param BackedEnum $rejectedStatus The rejected status enum value.
     * @param string $remarks Optional remarks.
     * @param string $statusColumn The status column name (default: 'Status').
     * @return bool
     * @throws ErroredException
     */
    public function reject($model, User $actor, BackedEnum $rejectedStatus, string $remarks = 'Rejected', string $statusColumn = 'Status'): bool
    {
        $status = self::codeDetail($rejectedStatus, $this->codeId);
        
        return $this->rejectAction(
            $actor, 
            $status, 
            $model::getPrimaryKey(), 
            $model->getKey(), 
            $remarks, 
            $statusColumn
        );
    }

    /**
     * Get workflow history for a module.
     * 
     * @param string $morphAlias The morph alias for the model (e.g., from $model::getPrimaryKey()).
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
        return $model->workflowHistory()
            ->with(['creator', 'status', 'stage'])
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
        return parent::canApprove(
            get_class($model),
            $model->getKey(), 
            $user
        );
    }

    //other generic methods
    public function cancel($model, User $actor, string $reason = 'Cancelled'): bool
    {
        return $this->cancelWorkflow($actor, $model::getPrimaryKey(), $model->getKey(), $reason);
    }

    public function getStatus($model): array
    {
        return $this->getWorkflowStatus($model::getPrimaryKey(), $model->getKey());
    }
}