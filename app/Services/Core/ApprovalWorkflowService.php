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
     * Get CodeDetail by enum and CodeID
     * 
     * @throws ErroredException
     */
    public static function codeDetail(BackedEnum $status, string $CodeID): CodeDetail
    {
        $code = CodeDetail::query()
            ->where('CodeID', $CodeID)
            ->where('Value', $status->value)
            ->first();
            
        if ($code instanceof CodeDetail) {
            Log::info("CodeDetail retrieved", [
                'CodeID' => $CodeID,
                'Value' => $status->value,
                'ID' => $code->ID,
                'Description' => $code->Description,
            ]);
            return $code;
        }
        
        Log::error("CodeDetail not found", [
            'CodeID' => $CodeID,
            'Value' => $status->value,
        ]);
        throw new ErroredException('Invalid Status');
    }

    /**
     * Get the first stage of a workflow for a given table
     */
    protected function getPermissionFromStage(string $table): ?WorkflowStage
    {
        Log::info('Getting workflow stage', ['table' => $table]);
        
        $workflow = Workflow::where('Source', $table)
            ->whereNull('DeletedOn')
            ->first();

        if (!$workflow) {
            Log::error("No workflow found", ['table' => $table]);
            return null;
        }

        Log::info("Workflow found", [
            'WorkFlowId' => $workflow->Id,
            'Source' => $workflow->Source,
        ]);

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
            'order' => $stage->Order,
            'count' => $stage->Count,
            'workflowTypeId' => $stage->WorkFlowTypeId,
        ]);

        return $stage;
    }

    /**
     * Get the current active stage id for a given table + sourceId
     */
    protected function getCurrentStageId(string $table, string|int $sourceId): ?int
    {
        // Get the most recent stage from pending approvals first
        $pendingStage = DB::table('t_WorkFlowPending')
            ->where('Source', $table)
            ->where('SourceID', (string)$sourceId)
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'desc')
            ->value('Stage');

        if ($pendingStage && is_numeric($pendingStage)) {
            Log::info("Current stage from pending", [
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $pendingStage,
            ]);
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
            Log::info("Current stage from history", [
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $history->Stage,
            ]);
            return is_numeric($history->Stage) ? (int)$history->Stage : null;
        }

        // If nothing found, return first stage
        $firstStage = $this->getPermissionFromStage($table);
        if ($firstStage) {
            Log::info("Using first stage as current", [
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $firstStage->Id,
            ]);
            return (int)$firstStage->Id;
        }

        Log::warning("No current stage found", [
            'table' => $table,
            'sourceId' => $sourceId,
        ]);
        return null;
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
    ): WorkflowHistory {
        try {
            $data = [
                'Source'     => $table,
                'SourceID'   => (string)$sourceId,
                'StatusId'   => $statusId,
                'Stage'      => (string)$stageId,
                'Amount'     => $amount,
                'Notes'      => $notes,
                // 'isApproved' => null,  //  Explicitly set to null (unprocessed)
                'CreatedBy'  => $actorId,
                'ModifiedBy' => $actorId,
                'CreatedOn'  => now(),
                'ModifiedOn' => now(),
            ];

            Log::info("Creating WorkflowHistory entry", [
                'data' => $data,
            ]);

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

            // Verify the entry was created
            $verifyEntry = DB::table('t_WorkFlowHistory')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                // ->where('isApproved', null)
                ->where('Stage', (string)$stageId)
                ->where('StatusId', $statusId)
                ->whereNull('DeletedOn')
                ->first();

            if (!$verifyEntry) {
                Log::error("WorkflowHistory entry not found after creation", [
                    'table' => $table,
                    'sourceId' => $sourceId,
                    'stageId' => $stageId,
                ]);
                throw new Exception("Failed to verify workflow history entry");
            }

            Log::info("WorkflowHistory entry created and verified", [
                'historyId' => $entry->Id,
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stageId,
                'statusId' => $statusId,
                'verifiedId' => $verifyEntry->Id ?? null,
            ]);
            
            return $entry;

        } catch (\Throwable $e) {
            Log::error("Exception creating WorkflowHistory entry", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stageId,
            ]);
            throw $e;
        }
    }

    /**
     * NEW: Approve action using the dynamic workflow processor
     */
    protected function approveAction(
        User $actor, 
        CodeDetail $status, 
        string $source, 
        string|int $sourceId, 
        string $remarks, 
        string $statusColumn = 'Status'
    ): bool {
        return $this->_executeWorkflowAction($actor, $status, $source, $sourceId, $remarks, $statusColumn);
    }

    /**
     * NEW: Reject action using the dynamic workflow processor
     */
    protected function rejectAction(
        User $actor, 
        CodeDetail $status, 
        string $source, 
        string|int $sourceId, 
        string $remarks, 
        string $statusColumn = 'Status'
    ): bool {
        return $this->_executeWorkflowAction($actor, $status, $source, $sourceId, $remarks, $statusColumn);
    }

    /**
     * NEW: Execute workflow action (approve/reject) via stored procedure
     */
    private function _executeWorkflowAction(
        User $actor, 
        CodeDetail $status, 
        string $source, 
        string|int $sourceId, 
        string $remarks, 
        string $statusColumn
    ): bool
    {
         // Resolve morph alias to actual model class
    $class = Relation::getMorphedModel($source);
    if (!($class && class_exists($class))) {
        throw new ErroredException('Invalid Related Entity');
    }
    
    // Determine the status value to set based on module (using your getStatusValueForModule method)
    $statusValueToSet = $this->getStatusValueForModule($source, $status);
    
    // Get the actual table name
    $table = (new $class)->getTable();

    Log::info("Executing workflow action", [
        'source_alias' => $source,
        'sourceId' => $sourceId,
        'resolved_class' => $class,
        'resolved_table' => $table,
        'actor' => $actor->Id,
        'actorName' => $actor->Name ?? $actor->UserID,
        'statusId' => $status->ID,
        'statusDescription' => $status->Description,
        'statusColumn' => $statusColumn,
        'statusValueToSet' => $statusValueToSet,  // Log the mapped value
    ]);

    try {
        DB::beginTransaction();

        Log::info("Calling p_ProcessWorkflowAction", [
            'table' => $table,
            'sourceId' => $sourceId,
            'userId' => $actor->Id,
            'userName' => $actor->Name ?? $actor->UserID,
            'statusId' => $status->ID,
            'notes' => $remarks,
            'statusValueToSet' => $statusValueToSet,
        ]);

        // Call stored procedure with corrected parameters
        $result = DB::select(
            'EXEC p_ProcessWorkflowAction 
                @Source = ?, 
                @SourceID = ?, 
                @UserID = ?, 
                @UserName = ?, 
                @Notes = ?, 
                @StatusColumn = ?, 
                @StatusID = ?, 
                @StatusValueToSet = ?',
            [
                $table,
                (string)$sourceId,
                $actor->Id,
                $actor->Name ?? $actor->UserID,
                $remarks,
                $statusColumn,
                $status->ID,  // BIGINT StatusId for t_WorkFlowHistory
                $statusValueToSet  // Mapped status value for source table
            ]
        );

        Log::info("p_ProcessWorkflowAction raw result", [
            'result' => $result,
        ]);

        // Check if stored procedure returned an error
        if (!empty($result) && isset($result[0]->Status)) {
            if ($result[0]->Status === 'ERROR') {
                Log::error("p_ProcessWorkflowAction returned ERROR", [
                    'message' => $result[0]->Message ?? 'Unknown error',
                ]);
                throw new ErroredException($result[0]->Message ?? 'Workflow action failed');
            }
            
            Log::info("p_ProcessWorkflowAction executed successfully", [
                'status' => $result[0]->Status,
                'message' => $result[0]->Message ?? null,
                'workflowStatus' => $result[0]->WorkflowStatus ?? null,
            ]);
        }

        DB::commit();

    } catch (ErroredException $e) {
        DB::rollBack();
        Log::error("ErroredException in _executeWorkflowAction", [
            'message' => $e->getMessage(),
        ]);
        throw $e;
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Error in _executeWorkflowAction', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'table' => $table,
            'sourceId' => $sourceId
        ]);
        throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
    }

    return true;
    }
        
        
    

    /**
     * Determine the status value to set based on module
     */
     private function getStatusValueForModule(string $source, CodeDetail $status): string
    {
        // Load mappings from config
        $mappings = config('workflow', []);

        // Check if the source has a mapping defined
        if (isset($mappings[$source]) && is_array($mappings[$source])) {
            $moduleMapping = $mappings[$source];

            // Look up the status description in the mapping
            if (isset($moduleMapping[$status->Description])) {
                return $moduleMapping[$status->Description];
            }

            // Optional: Check for variations (e.g., 'Approval' as alias for 'Approved')
            $aliases = [
                'Approval' => 'Approved',  // Map 'Approval' to 'Approved' if needed
            ];
            $normalizedDescription = $aliases[$status->Description] ?? $status->Description;
            if (isset($moduleMapping[$normalizedDescription])) {
                return $moduleMapping[$normalizedDescription];
            }
        }

        // Fallback: Use full description (original behavior for unmapped modules)
        Log::info("No mapping found for source '{$source}', using fallback", [
            'statusDescription' => $status->Description,
        ]);
        return strtolower($status->Description);
    }


    /**
     * Extract clean error message from SQL Server
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
     * Submit a record for approval workflow
     */
    protected function submittedAction(
        User $actor, 
        CodeDetail $status, 
        $model, 
        string $source, 
        string|int $sourceId, 
        string $remarks
    ): bool {
        $class = Relation::getMorphedModel($source);
        if (!($class && class_exists($class))) {
            Log::error("Invalid morph alias", [
                'source' => $source,
            ]);
            throw new ErroredException('Invalid Related Entity');
        }

        try {
            DB::beginTransaction();

            $table = $model->getTable();
            $sourceId = $model->getKey();
            
            Log::info("=== STARTING WORKFLOW SUBMISSION ===", [
                'source_alias' => $source,
                'resolved_class' => $class,
                'table' => $table,
                'sourceId' => $sourceId,
                'actorId' => $actor->Id,
                'actorName' => $actor->Name ?? $actor->UserID,
                'statusId' => $status->ID,
                'statusDescription' => $status->Description,
            ]);
            
            // Check if already submitted
            $existingSubmission = DB::table('t_WorkFlowHistory')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                // ->where('isApproved', null)
                ->whereNull('DeletedOn')
                ->first();

            if ($existingSubmission) {
                Log::warning("Already submitted", [
                    'table' => $table,
                    'sourceId' => $sourceId,
                    'existingHistoryId' => $existingSubmission->Id ?? null,
                ]);
                throw new ErroredException("This item has already been submitted for approval");
            }

            Log::info("No existing submission found - proceeding");

            $stage = $this->getPermissionFromStage($table);

            if (!$stage || !$stage->PermissionId) {
                Log::error("No workflow configuration", [
                    'table' => $table,
                    'stageFound' => $stage !== null,
                    'permissionId' => $stage->PermissionId ?? null,
                ]);
                throw new ErroredException("No workflow configuration found for {$table}");
            }

            // Get amount if available
            $amount = $model->Amount ?? null;

            Log::info("=== CREATING WORKFLOW HISTORY ===", [
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stage->Id,
                'statusId' => $status->ID,
                'amount' => $amount,
                'actorId' => $actor->Id,
            ]);

            // Create history entry
            $history = $this->createHistoryEntry(
                $table,
                $sourceId,
                $status->ID,
                (int)$stage->Id,
                $actor->Id,
                $remarks,
                $amount
            );

            Log::info("=== WORKFLOW HISTORY CREATED ===", [
                'historyId' => $history->Id,
            ]);

            // Verify the status is "Submitted for Approval"
            $submittedStatusCheck = DB::table('t_CodeDetails')
                ->where('ID', $status->ID)
                ->first();

            Log::info("Status verification", [
                'statusId' => $status->ID,
                'statusDescription' => $submittedStatusCheck->Description ?? 'NOT FOUND',
                'expectedDescription' => 'Submitted for Approval',
            ]);

            DB::commit();
            Log::info("Transaction committed successfully");

              $model->refresh();
        Log::info("Model refreshed after workflow action", [
            'sourceId' => $sourceId,
            'newStatus' => $model->Status ?? 'N/A',
        ]);

            // Execute workflow stored procedure
            try {
                Log::info("=== CALLING p_ProcessWorkflowPending ===");
                
                // Check what the SP will see
                $historyForSP = DB::table('t_WorkFlowHistory')
                    ->where('Source', $table)
                    ->where('SourceID', (string)$sourceId)
                    ->whereNull('DeletedOn')
                    ->get();

                Log::info("History records SP will process", [
                    'count' => $historyForSP->count(),
                    'records' => $historyForSP->toArray(),
                ]);

                DB::statement("EXEC p_ProcessWorkflowPending");
                
                Log::info("p_ProcessWorkflowPending executed successfully");

                // Verify pending entries were created
                $pendingEntries = DB::table('t_WorkFlowPending')
                    ->where('Source', $table)
                    ->where('SourceID', (string)$sourceId)
                    ->whereNull('DeletedOn')
                    ->get();

                Log::info("=== PENDING ENTRIES AFTER SP ===", [
                    'count' => $pendingEntries->count(),
                    'entries' => $pendingEntries->toArray(),
                ]);

                if ($pendingEntries->count() === 0) {
                    Log::error("CRITICAL: No pending entries created after SP execution!", [
                        'table' => $table,
                        'sourceId' => $sourceId,
                        'historyId' => $history->Id,
                    ]);
                }

            } catch (\Throwable $e) {
                Log::error('=== ERROR EXECUTING p_ProcessWorkflowPending ===', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'table' => $table,
                    'sourceId' => $sourceId,
                ]);
                throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
            }

        } catch (ErroredException $e) {
            DB::rollBack();
            Log::error("ErroredException in submittedAction", [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in submittedAction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
        }

        return true;
    }

    /**
     * Get workflow history
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
     * Check if user can approve
     */
    public function canApprove(string $source, string|int $sourceId, User $user): bool
    {
        $class = Relation::getMorphedModel($source) ?? $source;
        if (!($class && class_exists($class))) {
            return false;
        }

        $table = (new $class)->getTable();

        $workflow = Workflow::where('Source', $table)
            ->whereNull('DeletedOn')
            ->first();

        if (!$workflow) {
            return false;
        }

        $stage = WorkflowStage::where('WorkFlowId', $workflow->Id)
            ->whereNull('DeletedOn')
            ->orderBy('Order')
            ->first();

        if (!$stage || !$stage->PermissionId) {
            return false;
        }
         $hasPending = WorkflowPending::where('Source', $table)
            ->where('SourceID', (string)$sourceId)
            ->where('UserId', $user->Id)
            ->whereNull('DeletedOn')
            ->exists();
        if (!$hasPending) {
            return false;
        }

        // Maker-checker rule
        $maker = WorkflowHistory::where('Source', $table)
            ->where('SourceID', $sourceId)
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn')
            ->first();

        if ($maker && $maker->CreatedBy == $user->Id) {
            return false;
        }

        // Check permissions
        if ($user->hasRole('Admin') || $user->hasPermission($stage->PermissionId)) {
            return true;
        }

        // Check pending
        return WorkflowPending::where('Source', $table)
            ->where('SourceID', $sourceId)
            ->where('UserId', $user->Id)
            ->whereNull('DeletedOn')
            ->exists();
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

        $pendingApprovers = DB::table('t_WorkFlowPending as p')
            ->join('t_Users as u', 'p.UserId', '=', 'u.Id')
            ->where('p.Source', $table)
            ->where('p.SourceID', (string)$sourceId)
            ->where('p.Stage', (string)$currentStageId)
            ->whereNull('p.DeletedOn')
            ->whereNull('u.DeletedOn')
            ->select('u.Id', 'u.Name', 'u.Email')
            ->get()
            ->toArray();

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
            ->orderBy('h.CreatedOn', 'desc')
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
     * Cancel/withdraw workflow
     */
    public function cancelWorkflow(
        User $actor, 
        string $source, 
        string|int $sourceId, 
        string $reason = 'Cancelled by submitter'
    ): bool {
        $class = Relation::getMorphedModel($source);
        if (!($class && class_exists($class))) {
            throw new ErroredException('Invalid Related Entity');
        }

        $table = (new $class)->getTable();

        // Check if user is submitter
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

            // Soft delete pending and history
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
            ]);

            DB::commit();
            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error cancelling workflow', [
                'error' => $e->getMessage(),
            ]);
            throw new ErroredException("Failed to cancel workflow: " . $e->getMessage());
        }
    }
}