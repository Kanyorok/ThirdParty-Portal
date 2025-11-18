<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Services\Workflow\ApprovalWorkflow;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcurementPlanWorkflow extends ApprovalWorkflow
{
    private string $configKey = 'PlanID';
    private string $codeId = 'PROCUREMENT_PLAN_STATUS';

    public function __construct()
    {
        parent::__construct($this->codeId);
    }

    /**
     * Get the mapped status value from config
     */
    private function getStatusFromConfig(string $description): string
    {
        $mappings = config('workflow.' . $this->configKey, []);

        if (!array_key_exists($description, $mappings)) {
            Log::error("Status mapping not found in config", [
                'configKey' => $this->configKey,
                'description' => $description,
                'availableMappings' => array_keys($mappings)
            ]);
            throw new ErroredException("Status '{$description}' not found in workflow configuration");
        }

        return $mappings[$description];
    }

    /**
     * Get CodeDetail ID by status value
     * This bridges config-based statuses with CodeDetail table
     */
    private function getCodeDetailId(string $statusValue): int
    {
        $codeDetail = DB::table('t_CodeDetails')
            ->where('CodeID', $this->codeId)
            ->where('Value', $statusValue)
            ->whereNull('DeletedOn')
            ->first();

        if (!$codeDetail) {
            Log::error("CodeDetail not found", [
                'codeId' => $this->codeId,
                'statusValue' => $statusValue,
            ]);
            throw new ErroredException("Status code '{$statusValue}' not found in CodeDetails for {$this->codeId}");
        }

        Log::info("Found CodeDetail", [
            'id' => $codeDetail->ID,
            'value' => $codeDetail->Value,
            'description' => $codeDetail->Description,
        ]);

        return (int)$codeDetail->ID;
    }

    /**
     * Submit plan for approval
     * Uses config-based status but retrieves CodeDetail ID
     */
    public function submit($model, User $actor, BackedEnum $pendingStatus, string $remarks = 'Submitted'): bool
    {
        if (!$model instanceof ConsolidatedProcurementPlan) {
            throw new ErroredException("Invalid model passed to ProcurementPlanWorkflow::submit");
        }

        $table = $model->getTable();
        $sourceId = $model->getKey();
        
        // Get mapped status value from config
        $statusValue = $this->getStatusFromConfig('Pending');
        
        // Get corresponding CodeDetail ID
        $statusId = $this->getCodeDetailId($statusValue);

        Log::info("ProcurementPlanWorkflow: Submitting plan", [
            'planId' => $model->PlanID,
            'table' => $table,
            'pendingStatus' => $pendingStatus->value,
            'mappedStatus' => $statusValue,
            'codeDetailId' => $statusId,
        ]);

        // Prevent double submission
        $existingSubmission = DB::table('t_WorkFlowHistory')
            ->where('Source', $table)
            ->where('SourceID', (string)$sourceId)
            ->where('isApproved', null)
            ->whereNull('DeletedOn')
            ->first();

        if ($existingSubmission) {
            Log::warning("Already submitted", ['table' => $table, 'sourceId' => $sourceId]);
            throw new ErroredException("This plan has already been submitted for approval");
        }

        // Get first stage from workflow
        $workflow = DB::table('t_WorkFlows')
            ->where('Source', $table)
            ->whereNull('DeletedOn')
            ->first();

        if (!$workflow) {
            throw new ErroredException("No workflow configuration found for {$table}");
        }

        $stage = DB::table('t_WorkFlowStages')
            ->where('WorkFlowId', $workflow->Id)
            ->whereNull('DeletedOn')
            ->orderBy('Order', 'asc')
            ->first();

        if (!$stage || !$stage->PermissionId) {
            throw new ErroredException("No valid workflow stage found");
        }

        try {
            // Create workflow history with CodeDetail ID (no transaction here)
            DB::table('t_WorkFlowHistory')->insert([
                'Source' => $table,
                'SourceID' => (string)$sourceId,
                'StatusId' => $statusId, // Use CodeDetail ID
                'Stage' => (string)$stage->Id,
                'Amount' => $model->Amount ?? null,
                'Notes' => $remarks,
                'isApproved' => null,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);

            Log::info("Workflow history created", [
                'table' => $table,
                'sourceId' => $sourceId,
                'stageId' => $stage->Id,
                'statusId' => $statusId,
            ]);

            // Call the stored procedure (it manages its own transaction)
            DB::statement("
                EXEC p_ProcessWorkflowPending
                    @Source = :source,
                    @SourceID = :sourceId,
                    @StageID = :stageId
            ", [
                'source' => $table,
                'sourceId' => (string)$sourceId,
                'stageId' => (int)$stage->Id,
            ]);

            Log::info("Pending approvers created successfully");
            
            return true;

        } catch (\Throwable $e) {
            Log::error('Error in submit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Try to rollback the workflow history we just created
            try {
                DB::table('t_WorkFlowHistory')
                    ->where('Source', $table)
                    ->where('SourceID', (string)$sourceId)
                    ->where('StatusId', $statusId)
                    ->where('CreatedBy', $actor->Id)
                    ->whereNull('DeletedOn')
                    ->orderBy('CreatedOn', 'desc')
                    ->limit(1)
                    ->update([
                        'DeletedOn' => now(),
                        'DeletedBy' => $actor->Id,
                    ]);
            } catch (\Throwable $rollbackError) {
                Log::error("Failed to rollback workflow history", [
                    'error' => $rollbackError->getMessage()
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Approve plan
     * Uses config-based status but retrieves CodeDetail ID
     */
    public function approve($model, User $actor, BackedEnum $approvedStatus, string $remarks = 'Approved', string $statusColumn = 'Status'): bool
    {
        if (!$model instanceof ConsolidatedProcurementPlan) {
            throw new ErroredException("Invalid model passed to ProcurementPlanWorkflow::approve");
        }

        $table = $model->getTable();
        $sourceId = $model->getKey();
        
        // Get mapped status value from config
        $statusValue = $this->getStatusFromConfig('Approved');
        
        // Get corresponding CodeDetail ID
        $statusId = $this->getCodeDetailId($statusValue);

        Log::info("ProcurementPlanWorkflow: Approving plan", [
            'planId' => $model->PlanID,
            'approvedStatus' => $approvedStatus->value,
            'mappedStatus' => $statusValue,
            'codeDetailId' => $statusId,
        ]);

        try {
            DB::beginTransaction();

            // Call stored procedure with CodeDetail ID and status value
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
                    $statusId, // Pass CodeDetail ID
                    $statusValue // Also pass config status value for final table update
                ]
            );

            Log::info("p_ProcessWorkflowAction result", ['result' => $result]);

            if (!empty($result) && isset($result[0]->Status)) {
                if ($result[0]->Status === 'ERROR') {
                    throw new ErroredException($result[0]->Message ?? 'Workflow action failed');
                }
            }

            DB::commit();
            
            Log::info("Plan approved successfully", [
                'planId' => $model->PlanID,
                'statusValue' => $statusValue,
                'statusId' => $statusId,
            ]);
            
            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in approve', [
                'error' => $e->getMessage(),
            ]);
            throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Reject plan
     * Uses config-based status but retrieves CodeDetail ID
     */
    public function reject($model, User $actor, BackedEnum $rejectedStatus, string $remarks = 'Rejected', string $statusColumn = 'Status'): bool
    {
        if (!$model instanceof ConsolidatedProcurementPlan) {
            throw new ErroredException("Invalid model passed to ProcurementPlanWorkflow::reject");
        }

        $table = $model->getTable();
        $sourceId = $model->getKey();
        
        // Get mapped status value from config
        $statusValue = $this->getStatusFromConfig('Rejected');
        
        // Get corresponding CodeDetail ID
        $statusId = $this->getCodeDetailId($statusValue);

        Log::info("ProcurementPlanWorkflow: Rejecting plan", [
            'planId' => $model->PlanID,
            'rejectedStatus' => $rejectedStatus->value,
            'mappedStatus' => $statusValue,
            'codeDetailId' => $statusId,
        ]);

        try {
            DB::beginTransaction();

            // Call stored procedure with CodeDetail ID and status value
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
                    $statusId, // Pass CodeDetail ID
                    $statusValue // Also pass config status value for final table update
                ]
            );

            Log::info("p_ProcessWorkflowAction result", ['result' => $result]);

            if (!empty($result) && isset($result[0]->Status)) {
                if ($result[0]->Status === 'ERROR') {
                    throw new ErroredException($result[0]->Message ?? 'Workflow action failed');
                }
            }

            DB::commit();
            
            Log::info("Plan rejected successfully", [
                'planId' => $model->PlanID,
                'statusValue' => $statusValue,
                'statusId' => $statusId,
            ]);
            
            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in reject', [
                'error' => $e->getMessage(),
            ]);
            throw new ErroredException($e->getMessage());
        }
    }

    /**
     * Public helper to get mapped status value from config
     */
    public function getMappedStatus(string $description): string
    {
        return $this->getStatusFromConfig($description);
    }
}