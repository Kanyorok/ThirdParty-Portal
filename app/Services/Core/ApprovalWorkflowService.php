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
     * Get the first stage of a workflow for a given source
     */
    protected function getPermissionFromStage(string $source): ?WorkflowStage
    {
          Log::info('DEBUG getPermissionFromStage called', [
        'source' => $source,
        'expected' => 't_DepartmentNeeds',
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
    ]);
        
        $workflow = Workflow::where('Source', $source)
            ->whereNull('DeletedOn')
            ->first();

        if (!$workflow) {
            Log::error("No workflow found for source={$source}");
            return null;
        }

        $stage = WorkflowStage::where('WorkFlowId', $workflow->Id)
            ->whereNull('DeletedOn')
            ->orderBy('Order', 'asc')
            ->first();

        if (!$stage) {
            Log::error("No valid stage found", [
                'WorkFlowId' => $workflow->Id,
                'Source' => $source,
            ]);
            return null;
        }

        if (!$stage->PermissionId) {
            Log::error("CRITICAL: Stage found but PermissionId is NULL", [
                'WorkFlowId' => $workflow->Id,
                'StageId' => $stage->Id,
                'StageName' => $stage->StageName,
                'Source' => $source,
            ]);
            // This is critical - the stored procedure will fail if PermissionId is NULL
            return null;
        }

        return $stage;
    }

    /**
     * Get the current active stage id for a given source + sourceId.
     * Prefers the latest WorkflowHistory row (non-deleted). Falls back to first stage if none.
     */
    protected function getCurrentStageId(string $source, string|int $sourceId): ?int
    {
        $history = WorkflowHistory::query()
            ->where('Source', $source)
            ->where('SourceID', (string)$sourceId)
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'desc')
            ->first();

        if ($history && !empty($history->Stage)) {
            // Stage stored as string, cast to int if numeric
            return is_numeric($history->Stage) ? (int)$history->Stage : null;
        }

        $firstStage = $this->getPermissionFromStage($source);
        return $firstStage ? (int)$firstStage->Id : null;
    }

    /**
     * Create a workflow history entry
     * NOTE: PermissionId is NOT stored in WorkflowHistory - it comes from WorkflowStages via JOIN
     */
    protected function createHistoryEntry(
        string $source, 
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
                'Source'     => $source,
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
                    'source' => $source,
                    'sourceId' => $sourceId,
                    'stageId' => $stageId,
                    'data' => $data,
                ]);
                throw new Exception("Failed to create workflow history entry");
            }

            Log::info("WorkflowHistory entry created", [
                'historyId' => $entry->Id,
                'source' => $source,
                'sourceId' => $sourceId,
                'stageId' => $stageId,
                'statusId' => $statusId,
            ]);
            
            return $entry;

        } catch (\Throwable $e) {
            Log::error("Exception creating WorkflowHistory entry", [
                'error' => $e->getMessage(),
                'source' => $source,
                'sourceId' => $sourceId,
                'stageId' => $stageId,
            ]);
            throw $e;
        }
    }

    /**
     * @param User $actor
     * @param CodeDetail $status //status to
     * @param string $source Class::getPrimaryKey
     * @param string|int $sourceId Class primary id
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
     * @throws ErroredException
     */
    

    //Execute action
    private function _execute(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks, string $statusColumn): bool
{
    $class = Relation::getMorphedModel($source);
    if (!($class && class_exists($class))) {
        throw new ErroredException('Invalid Related Entity');
    }
    $table = (new $class)->getTable();

    Log::info("DEBUG _execute called", [
        'source_param' => $source,
        'sourceId_param' => $sourceId,
        'resolved_table' => $table,
        'expected_table' => 't_DepartmentNeeds',
        'actor' => $actor->Id
    ]);

    try {
        DB::beginTransaction();

        // Determine current stage ID (from history or fallback to first stage)
        $currentStageId = $this->getCurrentStageId($table, $sourceId);
        if (!$currentStageId) {
            Log::error("Unable to determine current stage", [
                'table' => $table,
                'sourceId' => $sourceId,
            ]);
            throw new ErroredException("Cannot determine current workflow stage");
        }

        // Verify the stage has a valid PermissionId
        $stage = WorkflowStage::find($currentStageId);
        if (!$stage || !$stage->PermissionId) {
            Log::error("Stage is missing or has no PermissionId", [
                'stageId' => $currentStageId,
                'table' => $table,
                'sourceId' => $sourceId,
            ]);
            throw new ErroredException("Workflow stage configuration is invalid - missing PermissionId");
        }

        // Call p_ProcessWorkflowAction to handle the approval/rejection
        Log::info("Executing p_ProcessWorkflowAction");
        DB::statement('EXEC p_ProcessWorkflowAction @Source = ?, @SourceID = ?, @UserID = ?, @UserName = ?, @Notes = ?, @StatusColumn = ?, @StatusID = ?', [
            $table,
            (string)$sourceId,
            $actor->Id,
            $actor->name ?? $actor->UserID,  // Assuming 'name' field; adjust if needed
            $remarks,
            $statusColumn,
            $status->ID
        ]);

        Log::info("p_ProcessWorkflowAction executed successfully");

        // Commit transaction before calling the next SP
        DB::commit();

        // Now run p_ProcessWorkflowStages to evaluate if the item should move to the next stage
        try {
            Log::info("Executing p_ProcessWorkflowStages");
            DB::statement('EXEC p_ProcessWorkflowStages @PermissionId = ?', [$stage->PermissionId]);
            Log::info("p_ProcessWorkflowStages completed successfully");
        } catch (\Throwable $e) {
            Log::error('Error executing p_ProcessWorkflowStages', [
                'error' => $e->getMessage(),
                'source' => $table,
                'sourceId' => $sourceId,
            ]);
            throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
        }

    } catch (ErroredException $e) {
        DB::rollBack();
        throw $e;
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Error in _execute', [
            'error' => $e->getMessage(),
            'source' => $table,
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
        // Pattern 1: Extract message between [SQL Server] and next bracket or end
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
     * @param CodeDetail $status //status to
     * @param mixed $model The model instance being submitted
     * @param string $source Class::getPrimaryKey
     * @param string|int $sourceId Class primary id
     * @param string $remarks
     * @return bool
     * @throws ErroredException
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
            
            // Get the first stage for this workflow
            $stage = $this->getPermissionFromStage($table);

            if (!$stage) {
                Log::error("No workflow stage found", [
                    'source' => $table,
                    'sourceId' => $sourceId,
                ]);
                throw new ErroredException("No workflow configuration found for {$table}");
            }

            if (!$stage->PermissionId) {
                Log::error("Stage found but PermissionId is NULL - SP will fail", [
                    'source' => $table,
                    'sourceId' => $sourceId,
                    'stageId' => $stage->Id,
                    'stageName' => $stage->StageName,
                    'workflowId' => $stage->WorkFlowId,
                ]);
                throw new ErroredException("Workflow stage '{$stage->StageName}' is missing PermissionId. Please fix the workflow configuration in t_WorkFlowStages table.");
            }

            Log::info("Creating workflow history entry for submission", [
                'source' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stage->Id,
                'stageName' => $stage->StageName,
                'stagePermissionId' => $stage->PermissionId,
                'statusId' => $status->ID,
            ]);

            // Create workflow history record with Stage = stage id
            // The stored procedure will JOIN this with t_WorkFlowStages to get PermissionId
            $history = $this->createHistoryEntry(
                $table,
                $sourceId,
                $status->ID,
                (int)$stage->Id,
                $actor->Id,
                $remarks,
                null // amount
            );

            Log::info("WorkflowHistory created successfully", [
                'historyId' => $history->Id,
                'source' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stage->Id,
                'stageName' => $stage->StageName,
            ]);

            // Commit transaction BEFORE calling stored procedures
            DB::commit();

            // Execute workflow stored procedures
            Log::info("Executing workflow stored procedures");
            
            try {
                Log::info("Calling p_ProcessWorkflowPending");
                DB::statement("EXEC p_ProcessWorkflowPending");
                Log::info("p_ProcessWorkflowPending executed successfully");
                
                Log::info("Calling p_ProcessWorkflowStages");
                DB::statement('EXEC p_ProcessWorkflowStages @PermissionId = ?', [$stage->PermissionId]);
                Log::info("p_ProcessWorkflowStages executed successfully");
                
            } catch (\Throwable $e) {
                Log::error('Error executing workflow stored procedures', [
                    'error' => $e->getMessage(),
                    'source' => $table,
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
            Log::warning("ROLLBACK triggered — all DB writes undone", [
    'source' => $table ?? 'unknown',
    'sourceId' => $sourceId ?? null,
]);
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            Log::warning("ROLLBACK triggered — all DB writes undone", [
    'source' => $table ?? 'unknown',
    'sourceId' => $sourceId ?? null,
]);
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
     * @param CodeDetail $status //status to
     * @param string $source Class::getPrimaryKey
     * @param string|int $sourceId Class primary id
     * @param string $remarks
     * @param string $statusColumn
     * @return bool
     * @throws ErroredException
     */
    protected function rejectAction(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks, string $statusColumn = 'Status'): bool
    {
        return $this->_execute($actor, $status, $source, $sourceId, $remarks, $statusColumn);
    }

    private function getSubmissionStatusId(): int
    {
        return CodeDetail::where('Description', 'Submitted for Approval')
            ->value('ID');
    }

    /**
     * @param string $source Model::getPrimaryKey
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
}