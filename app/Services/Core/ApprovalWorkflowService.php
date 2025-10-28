<?php

namespace App\Services\Core;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\Approval\CodeDetail;
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
        $history = WorkflowHistory::query()
            ->where('Source', $table)
            ->where('SourceID', (string)$sourceId)
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'desc')
            ->first();

        if ($history && !empty($history->Stage)) {
            return is_numeric($history->Stage) ? (int)$history->Stage : null;
        }

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
     * Execute workflow action (approve/reject)
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

            // Use table name for workflow operations
            $currentStageId = $this->getCurrentStageId($table, $sourceId);
            if (!$currentStageId) {
                Log::error("Unable to determine current stage", [
                    'table' => $table,
                    'sourceId' => $sourceId,
                ]);
                throw new ErroredException("Cannot determine current workflow stage");
            }

            $stage = WorkflowStage::find($currentStageId);
            if (!$stage || !$stage->PermissionId) {
                Log::error("Stage is missing or has no PermissionId", [
                    'stageId' => $currentStageId,
                    'table' => $table,
                    'sourceId' => $sourceId,
                ]);
                throw new ErroredException("Workflow stage configuration is invalid - missing PermissionId");
            }

            Log::info("Calling p_ProcessWorkflowAction", [
                'table' => $table,
                'sourceId' => $sourceId,
                'userId' => $actor->Id,
                'statusId' => $status->ID,
                'stagePermissionId' => $stage->PermissionId,
            ]);

            DB::statement('EXEC p_ProcessWorkflowAction @Source = ?, @SourceID = ?, @UserID = ?, @UserName = ?, @Notes = ?, @StatusColumn = ?, @StatusID = ?', [
                $table,
                (string)$sourceId,
                $actor->Id,
                $actor->Name ?? $actor->UserID,
                $remarks,
                $statusColumn,
                $status->ID
            ]);

            Log::info("p_ProcessWorkflowAction executed successfully");

            DB::commit();

            // Call p_ProcessWorkflowStages AFTER commit with the stage's PermissionId
            try {
                Log::info("Calling p_ProcessWorkflowStages", [
                    'permissionId' => $stage->PermissionId,
                ]);
                
                DB::statement('EXEC p_ProcessWorkflowStages @PermissionId = ?', [$stage->PermissionId]);
                
                Log::info("p_ProcessWorkflowStages completed successfully");
            } catch (\Throwable $e) {
                Log::error('Error executing p_ProcessWorkflowStages', [
                    'error' => $e->getMessage(),
                    'table' => $table,
                    'sourceId' => $sourceId,
                    'permissionId' => $stage->PermissionId,
                ]);
                // Don't throw - the approval was recorded, stage progression failed
                // This allows manual intervention if needed
            }

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
     * @param User $actor
     * @param CodeDetail $status
     * @param mixed $model The model instance being submitted
     * @param string $source Morph alias (e.g., 'department_needs')
     * @param string|int $sourceId
     * @param string $remarks
     * @return bool
     * @throws ErroredException
     */
    protected function submittedAction(User $actor, CodeDetail $status, $model, string $source, string|int $sourceId, string $remarks): bool
    {
        // Resolve morph alias to actual model class
        $class = Relation::getMorphedModel($source);
        if (!($class && class_exists($class))) {
            throw new ErroredException('Invalid Related Entity');
        }

        try {
            DB::beginTransaction();

            // Get table name from the actual model instance
            $table = $model->getTable();
            $sourceId = $model->getKey();
            
            Log::info("Submitting workflow", [
                'source_alias' => $source,
                'resolved_class' => $class,
                'table' => $table,
                'sourceId' => $sourceId,
                'actorId' => $actor->Id,
            ]);
            
            // Use table name to get workflow stage
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

            Log::info("Creating workflow history entry", [
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stage->Id,
                'stageName' => $stage->StageName,
                'permissionId' => $stage->PermissionId,
                'statusId' => $status->ID,
                'actorId' => $actor->Id,
            ]);

            // Create history with table name
            $history = $this->createHistoryEntry(
                $table,
                $sourceId,
                $status->ID,
                (int)$stage->Id,
                $actor->Id,
                $remarks,
                null
            );

            Log::info("WorkflowHistory created", [
                'historyId' => $history->Id,
                'table' => $table,
                'sourceId' => $sourceId,
            ]);

            // Commit transaction BEFORE calling stored procedures
            DB::commit();

            // Execute workflow stored procedures
            try {
                Log::info("Calling p_ProcessWorkflowPending");
                DB::statement("EXEC p_ProcessWorkflowPending");
                Log::info("p_ProcessWorkflowPending executed successfully");
                
                Log::info("Calling p_ProcessWorkflowStages", [
                    'permissionId' => $stage->PermissionId,
                ]);
                DB::statement('EXEC p_ProcessWorkflowStages @PermissionId = ?', [$stage->PermissionId]);
                Log::info("p_ProcessWorkflowStages executed successfully");
                
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
            ->limit($limit)
            ->get();
    }

    /**
     * Check if a user can approve/reject (maker-checker validation)
     */
  public function canApprove(string $source, string|int $sourceId, User $user): bool
{
    // 1️⃣ Resolve the actual model and table
    $class = Relation::getMorphedModel($source);
    if (!($class && class_exists($class))) {
        Log::warning("Invalid morph alias in canApprove", ['source' => $source]);
        return false;
    }

    $table = (new $class)->getTable();

    // 2️⃣ Get the user who created (submitted) the record
    $submitterId = DB::table('t_WorkFlowHistory')
        ->where('Source', $table)
        ->where('SourceID', (string)$sourceId)
        ->whereNull('DeletedOn')
        ->orderBy('CreatedOn', 'asc')
        ->value('CreatedBy');

    if ($submitterId && $submitterId == $user->Id) {
        Log::info("Maker-checker: user cannot approve their own submission", [
            'userId' => $user->Id,
            'table' => $table,
            'sourceId' => $sourceId,
        ]);
        return false;
    }

    // 3️⃣ Get the CURRENT stage for this specific record (not the first stage)
    $currentStageId = $this->getCurrentStageId($table, $sourceId);
    
    if (!$currentStageId) {
        Log::warning("No current stage found for record", [
            'table' => $table,
            'sourceId' => $sourceId,
        ]);
        return false;
    }

    // Get the stage details
    $stage = WorkflowStage::find($currentStageId);
    
    if (!$stage || !$stage->PermissionId) {
        Log::warning("No workflow stage or permission found", [
            'table' => $table,
            'sourceId' => $sourceId,
            'stageId' => $currentStageId,
        ]);
        return false;
    }

    // 4️⃣ Fetch the permission name from t_Permissions
    $permissionName = DB::table('t_Permissions')
        ->where('Id', $stage->PermissionId)
        ->value('Name');

    if (!$permissionName) {
        Log::warning("Permission not found for stage", [
            'table' => $table,
            'sourceId' => $sourceId,
            'stageId' => $currentStageId,
            'permissionId' => $stage->PermissionId,
        ]);
        return false;
    }

    // 5️⃣ Check role and permission
    $hasApprovalAccess =
        $user->hasRole(['Admin', 'Super Admin']) ||
        $user->hasPermissionTo($permissionName);

    Log::info("Approval access check", [
        'userId' => $user->Id,
        'table' => $table,
        'sourceId' => $sourceId,
        'currentStageId' => $currentStageId,
        'stageName' => $stage->StageName ?? 'N/A',
        'roles' => $user->getRoleNames(),
        'userPermissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        'requiredPermission' => $permissionName,
        'hasAccess' => $hasApprovalAccess,
    ]);

    return $hasApprovalAccess;
}

}