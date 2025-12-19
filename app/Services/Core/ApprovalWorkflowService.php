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

            return (int)$firstStage->Id;
        }

        Log::warning("No current stage found", [
            'table' => $table,
            'sourceId' => $sourceId,
        ]);
        return null;
    }

    /**
     * Get the status column name for a given morph alias/table
     */
    private function getStatusColumnForTable(string $morphAlias, string $table): string
    {
        // Check configuration first
        $columnMappings = config('workflow.status_columns', []);

        if (isset($columnMappings[$morphAlias])) {
            Log::info("Found status column from config", [
                'morphAlias' => $morphAlias,
                'column' => $columnMappings[$morphAlias],
            ]);
            return $columnMappings[$morphAlias];
        }

        // Check if table has ApprovalStatus column
        $hasApprovalStatus = DB::getSchemaBuilder()
            ->hasColumn($table, 'ApprovalStatus');

        if ($hasApprovalStatus) {
            Log::info("Table has ApprovalStatus column", ['table' => $table]);
            return 'ApprovalStatus';
        }

        // Default to Status
        Log::info("Using default Status column", ['table' => $table]);
        return 'Status';
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
    ): array {
        $class = Relation::getMorphedModel($source);
        if (!($class && class_exists($class))) {
            throw new ErroredException('Invalid Related Entity');
        }

        $statusValueToSet = $this->getStatusValueForModule($source, $status);
        $table = (new $class)->getTable();



        // Get current stage ID BEFORE calling SP
        $currentStageId = $this->getCurrentStageId($table, $sourceId);


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


                // Always call advanceToNextStage - it handles both moving to next stage AND finalizing if no next stage exists
                $this->advanceToNextStage($table, $sourceId, $currentStageId, $actor->Id, $statusColumn);
            } else {
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



        try {
            // Start a SINGLE transaction for everything
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

            // Create workflow history (within the main transaction)
            $history = $this->createHistoryEntry(
                $table,
                $sourceId,
                $status->ID,
                (int)$stage->Id,
                $actor->Id,
                $remarks,
                $amount
            );



            // Call stored procedure (still within the same transaction)
            $result = DB::select(
                'EXEC p_ProcessWorkflowPending @Source = ?, @SourceID = ?, @StageID = ?',
                [
                    $table,
                    (string)$sourceId,
                    (int)$stage->Id
                ]
            );



            // Check for errors returned by SP
            if (!empty($result) && isset($result[0]->Status)) {
                if ($result[0]->Status === 'ERROR') {
                    Log::error("p_ProcessWorkflowPending returned ERROR", [
                        'message' => $result[0]->Message ?? 'Unknown error',
                    ]);
                    throw new ErroredException($result[0]->Message ?? 'Failed to create pending approvals');
                }
            }

            // Log state after creating pending approvals
            $this->logWorkflowState($table, $sourceId, 'AFTER_SUBMISSION');

            // Commit everything together
            DB::commit();


            return true;
        } catch (ErroredException $e) {
            DB::rollBack();
            Log::error("ErroredException in submittedAction", [
                'message' => $e->getMessage(),
                'table' => $table,
                'sourceId' => $sourceId,
            ]);
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in submittedAction', [
                'error' => $e->getMessage(),
                'table' => $table,
                'sourceId' => $sourceId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
        }
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
     * Check if there are more stages after the current one
     */
    private function hasNextStage(string $table, int $currentStageId): bool
    {
        $currentStage = WorkflowStage::find($currentStageId);
        if (!$currentStage) {
            return false;
        }

        $nextStage = DB::table('t_WorkFlowStages')
            ->where('WorkFlowId', $currentStage->WorkFlowId)
            ->where('Order', '>', $currentStage->Order)
            ->whereNull('DeletedOn')
            ->orderBy('Order', 'asc')
            ->first();

        return !is_null($nextStage);
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



            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error cancelling workflow', ['error' => $e->getMessage()]);
            throw new ErroredException("Failed to cancel workflow");
        }
    }

    //advancing to the nect stage
    private function advanceToNextStage(string $table, string|int $sourceId, ?int $currentStageId, int $userId, string $statusColumn = 'Status'): void
    {
        try {
            DB::beginTransaction();

            if (!$currentStageId) {
                Log::warning("Cannot advance: currentStageId is null", ['table' => $table, 'sourceId' => $sourceId]);
                DB::rollBack();
                return;
            }

            $currentStage = WorkflowStage::query()
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

            $nextStage = DB::table('t_WorkFlowStages')
                ->where('WorkFlowId', $workflowId)
                ->where('Order', '>', $currentOrder)
                ->whereNull('DeletedOn')
                ->orderBy('Order', 'asc')
                ->first();

            if ($nextStage) {

                // Retrieve the amount from the history table (mirrors SP logic)
                $amount = 0;
                try {
                    $historyAmountResult = DB::table('t_WorkFlowHistory')
                        ->where('Source', $table)
                        ->where('SourceID', (string)$sourceId)
                        ->whereNull('DeletedOn')
                        ->whereNotNull('Amount')
                        ->orderBy('CreatedOn', 'desc')
                        ->limit(1)
                        ->value('Amount');

                    $amount = $historyAmountResult ?? 0;
                } catch (\Throwable $e) {
                    Log::warning("Could not retrieve amount from history for next stage", ['error' => $e->getMessage()]);
                }

                // Determine effective permission for next stage based on amount
                $effectivePermissionId = $nextStage->PermissionId;
                $limitType = 'DEFAULT';

                if ($amount > 0) {
                    try {
                        $permissionResult = DB::select("
                        SELECT PermissionId, MaxAmount, LimitType
                        FROM dbo.f_getPermissionForAmount(?, ?)
                    ", [$nextStage->Id, $amount]);

                        if (!empty($permissionResult)) {
                            $effectivePermissionId = $permissionResult[0]->PermissionId;
                            $limitType = $permissionResult[0]->LimitType;
                        }
                    } catch (\Throwable $e) {
                        Log::error("Error determining amount-based permission", ['error' => $e->getMessage()]);
                    }
                }

                // Use the stored procedure to handle pending creation
                // This is more efficient and consistent with submission logic

                try {
                    $spResult = DB::select(
                        'EXEC p_ProcessWorkflowPending @Source = ?, @SourceID = ?, @StageID = ?',
                        [$table, (string)$sourceId, (int)$nextStage->Id]
                    );



                    if (!empty($spResult) && isset($spResult[0]->Status)) {
                        if ($spResult[0]->Status === 'ERROR') {
                            Log::error("p_ProcessWorkflowPending returned ERROR during advancement", [
                                'message' => $spResult[0]->Message ?? 'Unknown error',
                            ]);
                            throw new ErroredException($spResult[0]->Message ?? 'Failed to create pending approvals for next stage');
                        }
                    }

                    // Get the users who were just inserted for notification
                    $insertedUsers = DB::table('t_WorkFlowPending as p')
                        ->join('t_Users as u', 'p.UserId', '=', 'u.Id')
                        ->where('p.Source', $table)
                        ->where('p.SourceID', (string)$sourceId)
                        ->where('p.Stage', (string)$nextStage->Id)
                        ->whereNull('p.DeletedOn')
                        ->select('u.Id', 'u.Name', 'u.Email')
                        ->get();

                    // Notify next-stage approvers
                    if ($insertedUsers->count() > 0) {
                        $this->notifyNextStageApprovers($insertedUsers, $nextStage, $table, $sourceId);
                    }
                } catch (ErroredException $e) {
                    throw $e;
                } catch (\Throwable $e) {
                    Log::error("Error calling p_ProcessWorkflowPending for next stage", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw new ErroredException("Failed to advance to next stage: " . $e->getMessage());
                }

                DB::commit();
            } else {
                // No next stage - workflow fully approved


                try {
                    // Get the morph alias from table name
                    $morphAlias = array_search($table, array_map(function ($class) {
                        return (new $class)->getTable();
                    }, Relation::morphMap()));

                    if (!$morphAlias) {
                        // Fallback: try to determine from table name
                        $morphAlias = Str::snake(Str::singular(str_replace('t_', '', $table)));
                    }

                    // Get the approved status value for this module
                    $approvedStatusValue = $this->getFinalApprovedStatus($morphAlias, $table);

                    // Dynamically get the primary key column from the model
                    $primaryKeyColumn = 'Id';  // Default fallback
                    try {
                        $modelClass = Relation::getMorphedModel($morphAlias);
                        if ($modelClass && class_exists($modelClass)) {
                            $primaryKeyColumn = (new $modelClass)->getKeyName();  // E.g., 'id', 'PlanID', 'custom_id'
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Could not determine primary key for table {$table}, using default 'Id'", [
                            'error' => $e->getMessage(),
                            'morphAlias' => $morphAlias,
                        ]);
                    }



                    // Update the status column in the source table
                    DB::statement("
            UPDATE {$table}
            SET {$statusColumn} = ?,
                ModifiedBy = ?,
                ModifiedOn = GETDATE()
            WHERE {$primaryKeyColumn} = ?
        ", [$approvedStatusValue, $userId, $sourceId]);
                } catch (\Throwable $e) {
                    Log::error("Failed to update source table status", [
                        'error' => $e->getMessage(),
                        'table' => $table,
                        'sourceId' => $sourceId,
                    ]);
                    // Don't throw - workflow is complete, this is just a status update issue
                }

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
                    return $status;
                }
            }

            //  Get from the table's typical status values
            $tableStatus = DB::table('t_CodeDetails')
                ->where('CodeID', 'LIKE', '%' . $workflowSource . '%')
                ->where('Description', 'LIKE', '%Approved%')
                ->value('Value');

            if ($tableStatus) {
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

    /**
     * Get detailed approval status message for error/info feedback
     */
    public function getApprovalDetailsMessage(string $source, string|int $sourceId): string
    {
        $class = Relation::getMorphedModel($source) ?? $source;
        if (!($class && class_exists($class))) {
            return 'Invalid entity provided.';
        }
        $table = (new $class)->getTable();

        $currentStageId = $this->getCurrentStageId($table, $sourceId);
        if (!$currentStageId) {
            return 'Workflow has not started or is invalid.';
        }

        $stage = WorkflowStage::with('type_name')->find($currentStageId);
        if (!$stage) {
            return 'Current stage information could not be retrieved.';
        }

        // Remaining stages
        $remainingStages = DB::table('t_WorkFlowStages')
            ->where('WorkFlowId', $stage->WorkFlowId)
            ->where('Order', '>', $stage->Order)
            ->whereNull('DeletedOn')
            ->count();

        // Pending count (already approved vs required)
        $required = $stage->Count ?? 1;
        $type = $stage->type_name->Name ?? 'Any';

        // Approvals so far for this stage
        $approvedStatusId = DB::table('t_CodeDetails')->where('Description', 'Approved')->value('ID');
        $approvedCount = DB::table('t_WorkFlowHistory')
            ->where('Source', $table)
            ->where('SourceID', (string)$sourceId)
            ->where('Stage', (string)$currentStageId)
            ->where('StatusId', $approvedStatusId)
            ->whereNull('DeletedOn')
            ->distinct('CreatedBy')
            ->count('CreatedBy');

        $remainingApprovers = max(0, $required - $approvedCount);

        return "Approvers required: {$required} (Pending: {$remainingApprovers}). Approval Type: {$type}. Remaining Stages: {$remainingStages}.";
    }
}
