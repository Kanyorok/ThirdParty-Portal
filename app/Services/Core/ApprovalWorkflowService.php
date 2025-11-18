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
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Log;

abstract class ApprovalWorkflowService
{
    /**
     * Get CodeDetail by enum and CodeID
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
                'isApproved' => null,
                'CreatedBy'  => $actorId,
                'ModifiedBy' => $actorId,
                'CreatedOn'  => now(),
                'ModifiedOn' => now(),
            ];

            Log::info("Creating WorkflowHistory entry", ['data' => $data]);

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

            Log::info("WorkflowHistory entry created successfully", [
                'historyId' => $entry->Id,
                'table' => $table,
                'sourceId' => $sourceId,
            ]);
            
            return $entry;

        } catch (\Throwable $e) {
            Log::error("Exception creating WorkflowHistory entry", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Execute workflow action (approve/reject) via stored procedure
     */
    private function _executeWorkflowAction(
        User $actor, 
        CodeDetail $status, 
        string $source, 
        string|int $sourceId, 
        string $remarks, 
        string $statusColumn
    ): array
    {
        $class = Relation::getMorphedModel($source);
    if (!($class && class_exists($class))) {
        throw new ErroredException('Invalid Related Entity');
    }
    
    $statusValueToSet = $this->getStatusValueForModule($source, $status);
    $table = (new $class)->getTable();

    Log::info("=== EXECUTING WORKFLOW ACTION ===", [
        'source_alias' => $source,
        'sourceId' => $sourceId,
        'resolved_table' => $table,
        'actor' => $actor->Id,
        'statusId' => $status->ID,
        'statusDescription' => $status->Description,
    ]);

    // Get current stage ID BEFORE calling SP
    $currentStageId = $this->getCurrentStageId($table, $sourceId);
    Log::info("Current stage ID before action", ['currentStageId' => $currentStageId]);

    //  Check state BEFORE calling SP
    $this->logWorkflowState($table, $sourceId, 'BEFORE_ACTION');

    try {
        DB::beginTransaction();

        
        DB::statement("SET NOCOUNT OFF");
        
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
                $status->ID,
                $statusValueToSet
            ]
        );

        Log::info("p_ProcessWorkflowAction result", ['result' => $result]);

        if (!empty($result) && isset($result[0]->Status)) {
            if ($result[0]->Status === 'ERROR') {
                Log::error("p_ProcessWorkflowAction returned ERROR", [
                    'message' => $result[0]->Message ?? 'Unknown error',
                ]);
                throw new ErroredException($result[0]->Message ?? 'Workflow action failed');
            }
        }

        //  Check state AFTER calling SP
        $this->logWorkflowState($table, $sourceId, 'AFTER_ACTION');

        DB::commit();
        
        $result = [
            'success' => true,
            'message' => $result[0]->Message ?? 'Action recorded successfully',
            'workflowStatus' => $result[0]->WorkflowStatus ?? 'Unknown',
            'stageCompleted' => $result[0]->StageCompleted ?? false,
        ];

        //  Use the properly defined $currentStageId
        if ($result['stageCompleted']) {
            Log::info("Stage completed, advancing to next stage", [
                'table' => $table,
                'sourceId' => $sourceId,
                'currentStageId' => $currentStageId,
            ]);
            $this->advanceToNextStage($table, $sourceId, $currentStageId, $actor->Id);
        } else {
            Log::info("Stage not completed yet", [
                'table' => $table,
                'sourceId' => $sourceId,
                'currentStageId' => $currentStageId,
            ]);
        }
        
        return $result;

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
            'table' => $table,
            'sourceId' => $sourceId,
            'trace' => $e->getTraceAsString(),
        ]);
        throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
    }

    }

    /**
     * Log comprehensive workflow state for diagnostics
     */
    private function logWorkflowState(string $table, string|int $sourceId, string $stage): void
    {
        try {
            // Get approved status ID
            $approvedStatusId = DB::table('t_CodeDetails')
                ->where('Description', 'Approved')
                ->value('ID');

            // Count pending entries
            $pendingCount = DB::table('t_WorkFlowPending')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                ->whereNull('DeletedOn')
                ->count();

            // Get current stage from pending
            $currentStage = DB::table('t_WorkFlowPending')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                ->whereNull('DeletedOn')
                ->value('Stage');

            // Count approvals for current stage
            $approvalCount = 0;
            if ($currentStage) {
                $approvalCount = DB::table('t_WorkFlowHistory')
                    ->where('Source', $table)
                    ->where('SourceID', (string)$sourceId)
                    ->where('Stage', $currentStage)
                    ->where('StatusId', $approvedStatusId)
                    ->whereNull('DeletedOn')
                    ->distinct('CreatedBy')
                    ->count('CreatedBy');
            }

            // Get stage requirements
            $stageRequirements = null;
            if ($currentStage) {
                $stageRequirements = DB::table('t_WorkFlowStages')
                    ->where('Id', $currentStage)
                    ->select('Count', 'StageName', 'PermissionId')
                    ->first();
            }

            Log::info("=== WORKFLOW STATE: {$stage} ===", [
                'table' => $table,
                'sourceId' => $sourceId,
                'currentStage' => $currentStage,
                'stageName' => $stageRequirements->StageName ?? 'N/A',
                'pendingCount' => $pendingCount,
                'approvalCount' => $approvalCount,
                'requiredApprovals' => $stageRequirements->Count ?? 'N/A',
                'approvedStatusId' => $approvedStatusId,
            ]);

            // List all pending users
            $pendingUsers = DB::table('t_WorkFlowPending as p')
                ->join('t_Users as u', 'p.UserId', '=', 'u.Id')
                ->where('p.Source', $table)
                ->where('p.SourceID', (string)$sourceId)
                ->whereNull('p.DeletedOn')
                ->select('u.Id', 'u.Name', 'p.Stage')
                ->get();

            Log::info("Pending approvers:", ['users' => $pendingUsers->toArray()]);

            // List all approvals
            $approvals = DB::table('t_WorkFlowHistory as h')
                ->join('t_Users as u', 'h.CreatedBy', '=', 'u.Id')
                ->where('h.Source', $table)
                ->where('h.SourceID', (string)$sourceId)
                ->where('h.StatusId', $approvedStatusId)
                ->whereNull('h.DeletedOn')
                ->select('u.Id', 'u.Name', 'h.Stage', 'h.CreatedOn')
                ->orderBy('h.CreatedOn')
                ->get();

            Log::info("Approval history:", ['approvals' => $approvals->toArray()]);

        } catch (\Throwable $e) {
            Log::error("Failed to log workflow state", [
                'error' => $e->getMessage(),
                'stage' => $stage,
            ]);
        }
    }

    /**
     * Approve action
     */
    protected function approveAction(
        User $actor, 
        CodeDetail $status, 
        string $source, 
        string|int $sourceId, 
        string $remarks, 
        string $statusColumn = 'Status'
    ): bool {
        $result = $this->_executeWorkflowAction($actor, $status, $source, $sourceId, $remarks, $statusColumn);
        
        Log::info("Approve action completed", [
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'No message',
            'workflowStatus' => $result['workflowStatus'] ?? 'Unknown',
            'stageCompleted' => $result['stageCompleted'] ?? false,
        ]);
        
        return $result['success'] ?? false;
    }

    /**
     * Reject action
     */
    protected function rejectAction(
        User $actor, 
        CodeDetail $status, 
        string $source, 
        string|int $sourceId, 
        string $remarks, 
        string $statusColumn = 'Status'
    ): bool {
        $result = $this->_executeWorkflowAction($actor, $status, $source, $sourceId, $remarks, $statusColumn);
        return $result['success'] ?? false;
    }

    /**
     * Determine the status value to set based on module
     */
    private function getStatusValueForModule(string $source, CodeDetail $status): string
    {
        $mappings = config('workflow', []);

        if (isset($mappings[$source]) && is_array($mappings[$source])) {
            $moduleMapping = $mappings[$source];

            if (isset($moduleMapping[$status->Description])) {
                return $moduleMapping[$status->Description];
            }

            $aliases = ['Approval' => 'Approved'];
            $normalizedDescription = $aliases[$status->Description] ?? $status->Description;
            if (isset($moduleMapping[$normalizedDescription])) {
                return $moduleMapping[$normalizedDescription];
            }
        }

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
            Log::error("Invalid morph alias", ['source' => $source]);
            throw new ErroredException('Invalid Related Entity');
        }

        $table = $model->getTable();
        $sourceId = $sourceId ?? $model->getKey();

        Log::info("=== STARTING WORKFLOW SUBMISSION ===", [
            'source_alias' => $source,
            'table' => $table,
            'sourceId' => $sourceId,
            'actorId' => $actor->Id,
        ]);

        try {
            DB::beginTransaction();

            // Prevent double submission
            $existingSubmission = DB::table('t_WorkFlowHistory')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                ->where('isApproved', null)
                ->whereNull('DeletedOn')
                ->first();

            if ($existingSubmission) {
                Log::warning("Already submitted", ['table' => $table, 'sourceId' => $sourceId]);
                throw new ErroredException("This item has already been submitted for approval");
            }

            // Get first stage
            $stage = $this->getPermissionFromStage($table);
            if (!$stage || !$stage->PermissionId) {
                Log::error("No workflow configuration", ['table' => $table]);
                throw new ErroredException("No workflow configuration found for {$table}");
            }

            $amount = $model->Amount ?? null;

            // Create workflow history
            $history = $this->createHistoryEntry(
                $table,
                $sourceId,
                $status->ID,
                (int)$stage->Id,
                $actor->Id,
                $remarks,
                $amount
            );

            Log::info("=== WORKFLOW HISTORY CREATED ===", ['historyId' => $history->Id]);

            DB::commit();
            Log::info("Transaction committed successfully for WorkflowHistory");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creating workflow history', ['error' => $e->getMessage()]);
            throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
        }

        // Create pending approvers for first stage
        try {
            DB::statement("
                EXEC p_ProcessWorkflowPending
                    @Source = :source,
                    @SourceID = :sourceId,
                    @StageID = :stageId
            ", [
                'source' => $table,
                'sourceId' => (string)$sourceId,
                'stageId' => (int)$stage->Id
            ]);

            Log::info("First stage pending approvals created", [
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stage->Id,
            ]);

            // Log state after creating pending approvals
            $this->logWorkflowState($table, $sourceId, 'AFTER_SUBMISSION');

        } catch (\Throwable $e) {
            Log::error('Error executing p_ProcessWorkflowPending', ['error' => $e->getMessage()]);
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

        return true;
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
            ]);

            DB::commit();
            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error cancelling workflow', ['error' => $e->getMessage()]);
            throw new ErroredException("Failed to cancel workflow");
        }
    }

    private function advanceToNextStage(string $table, string|int $sourceId, ?int $currentStageId, int $userId): void
{
     try {
        DB::beginTransaction();

        //  Better logging
        Log::info("=== ADVANCING TO NEXT STAGE ===", [
            'table' => $table,
            'sourceId' => $sourceId,
            'currentStageId' => $currentStageId,
            'userId' => $userId,
        ]);

        if (!$currentStageId) {
            Log::warning("Cannot advance: currentStageId is null", ['table' => $table, 'sourceId' => $sourceId]);
            DB::rollBack();
            return;
        }

        // Get current stage order
      $currentStage = WorkflowStage::query() // Use the model defined in your earlier response
    ->where('Id', $currentStageId)
    ->whereNull('DeletedOn')
    ->first();

        if (!$currentStage) {
            Log::warning("Current stage not found", ['stageId' => $currentStageId]);
            DB::rollBack();
            return;
        }

        $currentOrder = $currentStage->Order;
        $workflowId = $currentStage->WorkFlowId;

        Log::info("Current stage info", [
            'stageId' => $currentStageId,
            'stageName' => $currentStage->StageName,
            'order' => $currentOrder,
            'workflowId' => $workflowId,
        ]);

        // Find next stage
        $nextStage = DB::table('t_WorkFlowStages')
            ->where('WorkFlowId', $workflowId)
            ->where('Order', '>', $currentOrder)
            ->whereNull('DeletedOn')
            ->orderBy('Order', 'asc')
            ->first();

        if ($nextStage) {
            Log::info("Next stage found", [
                'nextStageId' => $nextStage->Id,
                'nextStageName' => $nextStage->StageName,
                'nextOrder' => $nextStage->Order,
                'permissionId' => $nextStage->PermissionId,
            ]);

            // Clean up any existing pending for this stage
            $deleted = DB::table('t_WorkFlowPending')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                ->where('Stage', (string)$nextStage->Id)
                ->delete();

            if ($deleted > 0) {
                Log::info("Cleaned up existing pending entries", ['count' => $deleted]);
            }

            // Get submitter for maker-checker rule
            $submitterId = DB::table('t_WorkFlowHistory')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                ->whereNull('DeletedOn')
                ->orderBy('CreatedOn', 'asc')
                ->value('CreatedBy');

            Log::info("Submitter ID for maker-checker", ['submitterId' => $submitterId]);

            // Get approved/rejected status IDs
            $approvedStatusId = DB::table('t_CodeDetails')->where('Description', 'Approved')->value('ID');
            $rejectedStatusId = DB::table('t_CodeDetails')->where('Description', 'Rejected')->value('ID');

            //  Use the function properly with parameter binding to avoid SQL injection
            $usersToInsert = DB::select("
                SELECT DISTINCT u.Id, u.Name, u.Email
                FROM dbo.f_getUserWithPermission(?) perm
                JOIN t_Users u ON perm.Id = u.Id
                WHERE u.DeletedOn IS NULL
                  AND u.Id != ?
                  AND NOT EXISTS (
                      SELECT 1 
                      FROM t_WorkFlowHistory h
                      WHERE h.Source = ? 
                        AND h.SourceID = ? 
                        AND h.Stage = ?
                        AND h.CreatedBy = u.Id
                        AND h.StatusId IN (?, ?)
                        AND h.DeletedOn IS NULL
                  )
            ", [
                $nextStage->PermissionId,
                $submitterId ?? 0,
                $table,
                (string)$sourceId,
                (string)$nextStage->Id,
                $approvedStatusId,
                $rejectedStatusId
            ]);

            $usersToInsert = collect($usersToInsert);

            Log::info("Users eligible for next stage", ['count' => $usersToInsert->count()]);

            foreach ($usersToInsert as $user) {
                DB::table('t_WorkFlowPending')->insert([
                    'Source' => $table,
                    'SourceID' => (string)$sourceId,
                    'Stage' => (string)$nextStage->Id,
                    'UserId' => $user->Id,
                    'CreatedBy' => $userId,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => now(),
                ]);
            }

            Log::info("Advanced to next stage successfully", [
                'table' => $table,
                'sourceId' => $sourceId,
                'nextStageId' => $nextStage->Id,
                'nextStageName' => $nextStage->StageName,
                'insertedPending' => $usersToInsert->count(),
            ]);

            // Notify next-stage approvers
            if ($usersToInsert->count() > 0) {
                $this->notifyNextStageApprovers(collect($usersToInsert), $nextStage, $table, $sourceId);
            }

            DB::commit();
            Log::info("Stage advancement completed successfully");
            
        } else {
            //  No next stage - dynamically determine final status
            Log::info("No next stage found - finalizing workflow as fully approved");
            
            // Get the workflow to find the source table details
            $workflow = DB::table('t_WorkFlows')
                ->where('Id', $workflowId)
                ->first();
            
            if (!$workflow) {
                Log::error("Workflow not found for final approval", ['workflowId' => $workflowId]);
                DB::rollBack();
                return;
            }

            //  Get the approved status value for this specific module
            $finalStatus = $this->getFinalApprovedStatus($workflow->Source, $table);
            
            Log::info("Final status determined", [
                'workflowSource' => $workflow->Source,
                'table' => $table,
                'finalStatus' => $finalStatus,
            ]);

            // Update the main record with dynamic final status
            $updated = DB::table($table)
                ->where('Id', $sourceId)
                ->update([
                    'Status' => $finalStatus,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => now(),
                ]);

            if ($updated) {
                Log::info("Main record status updated successfully", [
                    'table' => $table,
                    'sourceId' => $sourceId,
                    'finalStatus' => $finalStatus,
                    'rowsAffected' => $updated,
                ]);
            } else {
                Log::warning("No rows affected when updating main record status", [
                    'table' => $table,
                    'sourceId' => $sourceId,
                ]);
            }

            // Mark all approved history entries as finalized
            $approvedStatusId = DB::table('t_CodeDetails')->where('Description', 'Approved')->value('ID');
            $historyUpdated = DB::table('t_WorkFlowHistory')
                ->where('Source', $table)
                ->where('SourceID', (string)$sourceId)
                ->where('StatusId', $approvedStatusId)
                ->whereNull('DeletedOn')
                ->update([
                    'isApproved' => 1,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => now(),
                ]);

            Log::info("Workflow history marked as approved", [
                'table' => $table,
                'sourceId' => $sourceId,
                'historyRecordsUpdated' => $historyUpdated,
            ]);

            Log::info("Workflow fully completed - final stage approved", [
                'table' => $table, 
                'sourceId' => $sourceId,
                'finalStatus' => $finalStatus,
                'workflowId' => $workflowId,
            ]);
            
            DB::commit();
        }
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("Failed to advance stage", [
            'error' => $e->getMessage(),
            'table' => $table,
            'sourceId' => $sourceId,
            'trace' => $e->getTraceAsString(),
        ]);
        throw $e;
    }
}



    /**
 *  Notify next-stage approvers via email
 */
private function notifyNextStageApprovers(Collection $users, object $nextStage, string $table, string|int $sourceId): void
{
     // Get system user ID (similar to SP)
        $systemUserId = DB::table('t_Users')
            ->where('UserID', 'ERPSYS')
            ->whereNull('DeletedOn')
            ->value('Id');

        if (!$systemUserId) {
            Log::error("System user ERPSYS not found for notifications");
            return;
        }

        foreach ($users as $user) {
            try {
                // Customize the message as needed
                $subject = 'New Workflow Approval Pending';
                $message = "Dear {$user->Name},\n\n" .
                    "A new approval is pending for your review.\n" .
                    "Stage: {$nextStage->StageName}\n" .
                    "Source: {$table}, ID: {$sourceId}\n\n" .
                    "Please log in to review and approve.\n\n" .
                    "Best regards,\nWorkflow System";

                // Call your email SP
                DB::statement("
                    EXEC p_sendNotificationEmail
                        @UserID = :userId,
                        @Subject = :subject,
                        @Message = :message,
                        @SenderId = :senderId,
                        @Source = :source,
                        @SourceID = :sourceId
                ", [
                    'userId' => $user->Id,
                    'subject' => $subject,
                    'message' => $message,
                    'senderId' => $systemUserId,
                    'source' => $table,
                    'sourceId' => (string)$sourceId,
                ]);

                Log::info("Notification sent to next-stage approver via SP", [
                    'userId' => $user->Id,
                    'stage' => $nextStage->StageName,
                ]);
            } catch (\Throwable $e) {
                Log::error("Failed to notify approver via SP", [
                    'userId' => $user->Id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

}

/**
 * NEW: Dynamically determine the final approved status for a module
 */
private function getFinalApprovedStatus(string $workflowSource, string $table): string
{
    try {
        // Check workflow configuration or module mapping
        $mappings = config('workflow', []);
        
        // Try to find the mapping for this workflow source
        if (isset($mappings[$workflowSource]) && isset($mappings[$workflowSource]['Approved'])) {
            return $mappings[$workflowSource]['Approved'];
        }
        
        // Look for common status patterns in the table's code details
        $commonApprovedStatuses = ['a', 'approved', 'complete', 'completed', 'done', 'final'];
        
        foreach ($commonApprovedStatuses as $status) {
            $exists = DB::table('t_CodeDetails')
                ->where('CodeID', 'LIKE', '%' . $workflowSource . '%')
                ->where('Value', $status)
                ->where('Description', 'LIKE', '%Approved%')
                ->exists();
                
            if ($exists) {
                Log::info("Found approved status from code details", [
                    'workflowSource' => $workflowSource,
                    'status' => $status,
                ]);
                return $status;
            }
        }
        
        //  Get from the table's typical status values
        $tableStatus = DB::table('t_CodeDetails')
            ->where('CodeID', 'LIKE', '%' . $workflowSource . '%')
            ->where('Description', 'LIKE', '%Approved%')
            ->value('Value');
            
        if ($tableStatus) {
            Log::info("Found approved status from table code details", [
                'workflowSource' => $workflowSource,
                'status' => $tableStatus,
            ]);
            return $tableStatus;
        }
        
        //  Fallback - analyze the table structure and common values
        $fallbackStatus = $this->determineFallbackStatus($table);
        
        Log::warning("Using fallback approved status", [
            'table' => $table,
            'workflowSource' => $workflowSource,
            'fallbackStatus' => $fallbackStatus,
        ]);
        
        return $fallbackStatus;
        
    } catch (\Throwable $e) {
        Log::error("Error determining final approved status", [
            'workflowSource' => $workflowSource,
            'table' => $table,
            'error' => $e->getMessage(),
        ]);
        
        // Ultimate fallback
        return 'a';
    }
}

/**
 *  Determine fallback status by analyzing the table
 */
    private function determineFallbackStatus(string $table): string
{
    try {
        // Convert table name to morph alias (adjust if needed)
        $morphAlias = Str::snake($table); // e.g., 'department_needs'

        // Load status mapping from config
        $statusMapping = config("workflow.{$morphAlias}", []);

        if (!empty($statusMapping)) {
            // Prefer 'Approved' if defined
            if (isset($statusMapping['Approved'])) {
                return $statusMapping['Approved'];
            }

            // Otherwise return the first mapped status
            return reset($statusMapping);
        }

    } catch (\Throwable $e) {
        Log::error("Error determining fallback status from config", [
            'table' => $table,
            'error' => $e->getMessage(),
        ]);
    }

    // Ultimate fallback if config is missing
    return 'a';
}

}