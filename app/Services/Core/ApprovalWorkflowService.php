<?php

namespace App\Services\Core;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Approval\WorkflowPending;
use App\Models\Core\Approval\Workflow;
use App\Models\Core\Approval\WorkflowStage;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

abstract class ApprovalWorkflowService
{
    /**
     * @throws ErroredException
     */
    public static function codeDetail(BackedEnum $status, string $CodeID): CodeDetail
    {
        $code = CodeDetail::query()->where('CodeID', $CodeID)->where('Value', $status->value)->first();
        if ($code instanceof CodeDetail) {
            return $code;
        }
        throw new ErroredException('Invalid Status');
    }

    /**
     * Get the first stage of a workflow for a given table
     */
    protected function getPermissionFromStage(string $table): ?WorkflowStage
    {
        Log::info('Getting workflow stage', [
            'table' => $table,
        ]);
        
        $workflow = Workflow::where('Source', $table)
            ->whereNull('DeletedOn')
            ->first();

        if (!$workflow) {
            Log::error("No workflow found", ['table' => $table]);
            return null;
        }

        $stage = WorkflowStage::where('WorkFlowId', $workflow->Id)
            ->whereNull('DeletedOn')
            ->orderBy('Order', 'asc')
            ->first();

        if (!$stage) {
            Log::error("No valid stage found", [
                'WorkFlowId' => $workflow->Id,
                'Table' => $table,
            ]);
            return null;
        }

        if (!$stage->PermissionId) {
            Log::error("CRITICAL: Stage found but PermissionId is NULL", [
                'WorkFlowId' => $workflow->Id,
                'StageId' => $stage->Id,
                'StageName' => $stage->StageName,
                'Table' => $table,
            ]);
            return null;
        }

        Log::info("Workflow stage retrieved successfully", [
            'table' => $table,
            'stageId' => $stage->Id,
            'stageName' => $stage->StageName,
            'permissionId' => $stage->PermissionId,
        ]);

        return $stage;
    }

    /**
     * Get the current active stage id for a given table + sourceId.
     */
    protected function getCurrentStageId(string $table, string|int $sourceId): ?int
    {
        //  Get the most recent stage from pending approvals first
        $pendingStage = DB::table('t_WorkFlowPending')
            ->where('Source', $table)
            ->where('SourceID', (string)$sourceId)
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'desc')
            ->value('Stage');

        if ($pendingStage && is_numeric($pendingStage)) {
            return (int)$pendingStage;
        }

        // Fallback to history if no pending
        $history = WorkflowHistory::query()
            ->where('Source', $table)
            ->where('SourceID', (string)$sourceId)
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'desc')
            ->first();

        if ($history && !empty($history->Stage)) {
            return is_numeric($history->Stage) ? (int)$history->Stage : null;
        }

        // If nothing found, return first stage
        $firstStage = $this->getPermissionFromStage($table);
        return $firstStage ? (int)$firstStage->Id : null;
    }

    /**
     * Create a workflow history entry
     */
    protected function createHistoryEntry(
        string $table, 
        string|int $sourceId, 
        int $statusId, 
        int $stageId, 
        int $actorId, 
        string $notes = null,
        float $amount = null
    ): WorkflowHistory
    {
        try {
            $data = [
                'Source'     => $table,
                'SourceID'   => (string)$sourceId,
                'StatusId'   => $statusId,
                'Stage'      => (string)$stageId,
                'Amount'     => $amount,
                'Notes'      => $notes,
                'CreatedBy'  => $actorId,
                'ModifiedBy' => $actorId,
                'CreatedOn'  => now(),
                'ModifiedOn' => now(),
            ];

            $entry = WorkflowHistory::create($data);

            if (!$entry->exists) {
                Log::error("Failed to insert WorkflowHistory", [
                    'table' => $table,
                    'sourceId' => $sourceId,
                    'stageId' => $stageId,
                    'data' => $data,
                ]);
                throw new Exception("Failed to create workflow history entry");
            }

            Log::info("WorkflowHistory entry created", [
                'historyId' => $entry->Id,
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stageId,
                'statusId' => $statusId,
            ]);
            
            return $entry;

        } catch (\Throwable $e) {
            Log::error("Exception creating WorkflowHistory entry", [
                'error' => $e->getMessage(),
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stageId,
            ]);
            throw $e;
        }
    }

    /**
     *  REMOVED - validateUserAction is no longer needed here
     * All validations (maker-checker, duplicate action, pending approval)
     * are now handled by p_ProcessWorkflowAction stored procedure
     */

    /**
     * @param User $actor
     * @param CodeDetail $status
     * @param string $source Morph alias (e.g., 'department_needs')
     * @param string|int $sourceId
     * @param string $remarks
     * @param string $statusColumn
     * @return bool
     * @throws ErroredException
     */
    protected function approveAction(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks, string $statusColumn = 'Status'): bool
    {
        return $this->_execute($actor, $status, $source, $sourceId, $remarks, $statusColumn);
    }

    /**
     *  Simplified _execute - let stored procedure handle validations
     */
    private function _execute(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks, string $statusColumn): bool
    {
        // Resolve morph alias to actual model class
        $class = Relation::getMorphedModel($source);
        if (!($class && class_exists($class))) {
            throw new ErroredException('Invalid Related Entity');
        }
        
        // Get the actual table name
        $table = (new $class)->getTable();

        Log::info("Executing workflow action", [
            'source_alias' => $source,
            'sourceId' => $sourceId,
            'resolved_class' => $class,
            'resolved_table' => $table,
            'actor' => $actor->Id,
            'action' => $status->Description ?? 'Unknown',
        ]);

        try {
            DB::beginTransaction();

            // : getCurrentStageId and validateUserAction
            // The stored procedure p_ProcessWorkflowAction now handles:
            // - Finding the current stage
            // - Maker-checker validation
            // - Duplicate action check
            // - Pending approval verification
            // - Permission validation

            Log::info("Calling p_ProcessWorkflowAction", [
                'table' => $table,
                'sourceId' => $sourceId,
                'userId' => $actor->Id,
                'statusId' => $status->ID,
            ]);

            // Call stored procedure directly
            $result = DB::select('EXEC p_ProcessWorkflowAction @Source = ?, @SourceID = ?, @UserID = ?, @UserName = ?, @Notes = ?, @StatusColumn = ?, @StatusID = ?', [
                $table,
                (string)$sourceId,
                $actor->Id,
                $actor->Name ?? $actor->UserID,
                $remarks,
                $statusColumn,
                $status->ID
            ]);

            // Check if stored procedure returned an error
            if (!empty($result) && isset($result[0]->Status)) {
                if ($result[0]->Status === 'ERROR') {
                    throw new ErroredException($result[0]->Message ?? 'Workflow action failed');
                }
                
                Log::info("p_ProcessWorkflowAction executed successfully", [
                    'status' => $result[0]->Status,
                    'message' => $result[0]->Message ?? null,
                    'workflowStatus' => $result[0]->WorkflowStatus ?? null,
                ]);
            }

            DB::commit();

            //  REMOVED: Call to p_ProcessWorkflowStages
            // It's now called internally by p_ProcessWorkflowAction (Step 10)
            // This prevents race conditions and ensures proper transaction handling

        } catch (ErroredException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in _execute', [
                'error' => $e->getMessage(),
                'table' => $table,
                'sourceId' => $sourceId
            ]);
            throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
        }

        return true;
    }

    /**
     * Extract the clean error message from SQL Server RAISERROR
     */
    private function _extractSqlServerError(string $errorMessage): string
    {
        if (preg_match('/\[SQL Server\]\s*(.+?)(?:\s*\[|$)/s', $errorMessage, $matches)) {
            $errorMessage = trim($matches[1]);
        } else if (preg_match('/SQLSTATE\[.*?\]:\s*(.+?)(?:\s*\(|$)/s', $errorMessage, $matches)) {
            $errorMessage = trim($matches[1]);
        }

        $cleanMessage = preg_replace('/\(Connection:.*?\)/', '', $errorMessage);
        $cleanMessage = preg_replace('/SQLSTATE\[.*?\]:\s*/', '', $cleanMessage);

        return trim($cleanMessage) ?: 'Database operation failed';
    }

    /**
     *  IMPROVED: Cleaner submission flow
     */
    protected function submittedAction(User $actor, CodeDetail $status, $model, string $source, string|int $sourceId, string $remarks): bool
{
    $class = Relation::getMorphedModel($source);
    if (!($class && class_exists($class))) {
        throw new ErroredException('Invalid Related Entity');
    }

    try {
        DB::beginTransaction();

        $table = $model->getTable();
        $sourceId = $model->getKey();
        
        Log::info("Submitting workflow", [
            'source_alias' => $source,
            'resolved_class' => $class,
            'table' => $table,
            'sourceId' => $sourceId,
            'actorId' => $actor->Id,
        ]);
        
        // Check if workflow has already been submitted
        $existingSubmission = DB::table('t_WorkFlowHistory')
            ->where('Source', $table)
            ->where('SourceID', (string)$sourceId)
            ->whereNull('DeletedOn')
            ->exists();

        if ($existingSubmission) {
            Log::warning("Workflow already submitted", [
                'table' => $table,
                'sourceId' => $sourceId,
                'actorId' => $actor->Id,
            ]);
            throw new ErroredException("This item has already been submitted for approval");
        }

        $stage = $this->getPermissionFromStage($table);

        if (!$stage) {
            Log::error("No workflow stage found", [
                'table' => $table,
                'sourceId' => $sourceId,
            ]);
            throw new ErroredException("No workflow configuration found for {$table}");
        }

        if (!$stage->PermissionId) {
            Log::error("Stage found but PermissionId is NULL", [
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stage->Id,
                'stageName' => $stage->StageName,
            ]);
            throw new ErroredException("Workflow stage '{$stage->StageName}' is missing PermissionId");
        }

        // Get amount if available (for AMT workflow types)
        $amount = null;
        if (isset($model->Amount)) {
            $amount = $model->Amount;
        }

        Log::info("Creating workflow history entry", [
            'table' => $table,
            'sourceId' => $sourceId,
            'stageId' => $stage->Id,
            'stageName' => $stage->StageName,
            'permissionId' => $stage->PermissionId,
            'statusId' => $status->ID,  // Use the passed status ID
            'actorId' => $actor->Id,
            'amount' => $amount,
        ]);

        // Create history with the provided status
        $history = $this->createHistoryEntry(
            $table,
            $sourceId,
            $status->ID,  // Fixed: Use $status->ID
            (int)$stage->Id,
            $actor->Id,
            $remarks,
            $amount
        );

        Log::info("WorkflowHistory created", [
            'historyId' => $history->Id,
            'table' => $table,
            'sourceId' => $sourceId,
        ]);

        DB::commit();

        // Execute workflow stored procedures AFTER commit
        try {
            Log::info("Calling p_ProcessWorkflowPending");
            DB::statement("EXEC p_ProcessWorkflowPending");
            Log::info("p_ProcessWorkflowPending executed successfully");
        } catch (\Throwable $e) {
            Log::error('Error executing workflow stored procedures', [
                'error' => $e->getMessage(),
                'table' => $table,
                'sourceId' => $sourceId,
                'historyId' => $history->Id,
                'stageId' => $stage->Id,
                'stagePermissionId' => $stage->PermissionId,
            ]);
            throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
        }

    } catch (QueryException $e) {
        DB::rollBack();
        Log::error('QueryException in submittedAction', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
        ]);
        throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
    } catch (ErroredException $e) {
        DB::rollBack();
        Log::warning("Transaction rolled back", [
            'table' => $table ?? 'unknown',
            'sourceId' => $sourceId ?? null,
        ]);
        throw $e;
    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Unexpected error in submittedAction', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw new ErroredException("Unexpected Error Occurred: " . $e->getMessage());
    }

    return true;
}

    /**
     * @param User $actor
     * @param CodeDetail $status
     * @param string $source Morph alias
     * @param string|int $sourceId
     * @param string $remarks
     * @param string $statusColumn
     * @return bool
     * @throws ErroredException
     */
    protected function rejectAction(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks, string $statusColumn = 'Status'): bool
    {
        return $this->_execute($actor, $status, $source, $sourceId, $remarks, $statusColumn);
    }

    /**
     * @param string $source Morph alias
     * @throws ErroredException
     */
    protected function historyData(string $source, int $limit = 1000): Collection
    {
        $class = Relation::getMorphedModel($source);
        if ($class && class_exists($class)) {
            $table = (new $class)->getTable();
        } else {
            throw new ErroredException('Invalid Related Entity');
        }

        return WorkflowHistory::query()
            ->where('Source', $table)
            ->with(['creator', 'status'])
            ->orderBy('CreatedOn', 'desc')  
            ->limit($limit)
            ->get();
    }

    /**
     * : More reliable canApprove check
     */
    public function canApprove(string $source, string|int $sourceId, User $user): bool
    {
      // 1. Resolve morph alias to actual model class
    $class = Relation::getMorphedModel($source) ?? $source;
    if (!($class && class_exists($class))) {
        Log::warning("Invalid morph alias: {$source}");
        return false;
    }

    // 2. Get the actual table 
    $table = (new $class)->getTable();

    // 3. Find workflow using the table name
    $workflow = Workflow::where('Source', $table)
        ->whereNull('DeletedOn')
        ->first();

    if (!$workflow) {
        Log::warning("No workflow found for table={$table}");
        return false;
    }

    // 4. Get current stage
    $stage = WorkflowStage::where('WorkFlowId', $workflow->Id)
        ->whereNull('DeletedOn')
        ->orderBy('Order')
        ->first();

    if (!$stage) {
        Log::warning("No workflow stage found for table={$table}");
        return false;
    }

    $permissionId = $stage->PermissionId;
    if (!$permissionId) {
        Log::warning("No permission linked to workflow stage ID={$stage->Id}");
        return false;
    }

    //  5. Maker-checker rule
    $maker = WorkflowHistory::where('Source', $table)
        ->where('SourceID', $sourceId)
        ->whereNull('DeletedOn')
        ->orderBy('CreatedOn')
        ->first();

    if ($maker && $maker->CreatedBy == $user->Id) {
        return false;
    }

    //  6. Allow Admin or users with the required permission
    if ($user->hasRole('Admin') || $user->hasPermission($permissionId)) {
        return true;
    }

    //  7. Otherwise, check if user has pending approval
    $pending = WorkflowPending::where('Source', $table)
        ->where('SourceID', $sourceId)
        ->where('UserId', $user->Id)
        ->whereNull('DeletedOn')
        ->exists();

    return $pending;
}
    
    

    /**
     * Get workflow status for a record
     */
    public function getWorkflowStatus(string $source, string|int $sourceId): array
    {
        $class = Relation::getMorphedModel($source);
        if (!($class && class_exists($class))) {
            throw new ErroredException('Invalid Related Entity');
        }

        $table = (new $class)->getTable();
        $currentStageId = $this->getCurrentStageId($table, $sourceId);

        if (!$currentStageId) {
            return [
                'hasWorkflow' => false,
                'currentStage' => null,
                'pendingApprovers' => [],
                'completedApprovals' => [],
            ];
        }

        $stage = WorkflowStage::find($currentStageId);

        // Get pending 
        $pendingApprovers = DB::table('t_WorkFlowPending as p')
            ->join('t_Users as u', 'p.UserId', '=', 'u.Id')
            ->where('p.Source', $table)
            ->where('p.SourceID', (string)$sourceId)
            ->where('p.Stage', (string)$currentStageId)
            ->whereNull('p.DeletedOn')
            ->whereNull('u.DeletedOn')  // Added: Exclude deleted users
            ->select('u.Id', 'u.Name', 'u.Email')
            ->get()
            ->toArray();

        // Get completed approvals for this stage
        $approvedStatusId = DB::table('t_CodeDetails')
            ->where('Description', 'Approved')
            ->value('ID');

        $completedApprovals = DB::table('t_WorkFlowHistory as h')
            ->join('t_Users as u', 'h.CreatedBy', '=', 'u.Id')
            ->where('h.Source', $table)
            ->where('h.SourceID', (string)$sourceId)
            ->where('h.Stage', (string)$currentStageId)
            ->where('h.StatusId', $approvedStatusId)
            ->whereNull('h.DeletedOn')
            ->select('u.Id', 'u.Name', 'u.Email', 'h.CreatedOn', 'h.Notes')
            ->orderBy('h.CreatedOn', 'desc')  //  Added: Order by date
            ->get()
            ->toArray();

        return [
            'hasWorkflow' => true,
            'currentStage' => [
                'id' => $stage->Id,
                'name' => $stage->StageName,
                'order' => $stage->Order,
            ],
            'pendingApprovers' => $pendingApprovers,
            'completedApprovals' => $completedApprovals,
            'totalPending' => count($pendingApprovers),
            'totalCompleted' => count($completedApprovals),
        ];
    }

    /**
     * Cancel/withdraw a workflow submission
     */
    public function cancelWorkflow(User $actor, string $source, string|int $sourceId, string $reason = 'Cancelled by submitter'): bool
    {
        $class = Relation::getMorphedModel($source);
        if (!($class && class_exists($class))) {
            throw new ErroredException('Invalid Related Entity');
        }

        $table = (new $class)->getTable();

        // Check if user is the submitter
        $submitterId = DB::table('t_WorkFlowHistory')
            ->where('Source', $table)
            ->where('SourceID', (string)$sourceId)
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'asc')
            ->value('CreatedBy');

        if (!$submitterId || $submitterId != $actor->Id) {
            throw new ErroredException("Only the submitter can cancel the workflow");
        }

        try {
            DB::beginTransaction();

            // Soft delete all pending approvals
            DB::table('t_WorkFlowPending')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                ->whereNull('DeletedOn')
                ->update([
                    'DeletedOn' => now(),
                    'DeletedBy' => $actor->Id,
                    'ModifiedOn' => now(),
                    'ModifiedBy' => $actor->Id,
                ]);

            // Soft delete all workflow history
            DB::table('t_WorkFlowHistory')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                ->whereNull('DeletedOn')
                ->update([
                    'DeletedOn' => now(),
                    'DeletedBy' => $actor->Id,
                    'ModifiedOn' => now(),
                    'ModifiedBy' => $actor->Id,
                ]); 

            Log::info("Workflow cancelled", [
                'table' => $table,
                'sourceId' => $sourceId,
                'actorId' => $actor->Id,
                'reason' => $reason,
            ]);

            DB::commit();
            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error cancelling workflow', [
                'error' => $e->getMessage(),
                'table' => $table,
                'sourceId' => $sourceId,
            ]);
            throw new ErroredException("Failed to cancel workflow: " . $e->getMessage());
        }
    }
}