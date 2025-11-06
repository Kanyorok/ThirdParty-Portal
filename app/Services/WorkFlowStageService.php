<?php

namespace App\Services;

use App\Models\Auth\User;
use App\Models\Core\Approval\WorkFlowStage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Core\Approval\WorkFlow;
use Illuminate\Support\Facades\DB;
use App\DTOs\WorkflowStageResult;
use App\Exceptions\ErroredException;

class WorkFlowStageService
{
    public function createStage(array $data)
    {
        /** @var User $user */
        $user = Auth::user();
        
        if (!$user || !$user->Id) {
            throw new ErroredException('User must be authenticated to create a workflow stage.');
        }
        
        DB::beginTransaction();
        
        try {
            // Fetch workflow with fresh data from database
            $workflow = WorkFlow::findOrFail($data['WorkFlowId']);

            // Validate final stage logic - Check if workflow already has a final stage
            if ($workflow->IsFinalStage) {
                throw new ErroredException('This workflow already has a final stage. You cannot add more stages to a workflow with a final stage.');
            }

            $isFinalStage = !empty($data['IsFinalStage']) && ($data['IsFinalStage'] == 1 || $data['IsFinalStage'] === true);

            // Compute next order
            $nextOrder = WorkFlowStage::where('WorkFlowId', $data['WorkFlowId'])->max('Order') + 1;

            // Fetch ModuleID from t_ModuleSources based on workflow's Source
            $moduleId = DB::table('t_ModuleSources')
                ->where('DocumentType', $workflow->Source)
                ->value('ModuleID');

            if (!$moduleId) {
                Log::warning('No module found for workflow source', [
                    'workflow_id' => $workflow->Id,
                    'source' => $workflow->Source,
                ]);
                throw new ErroredException('No module found for this workflow. Please ensure the workflow has a valid document type configured.');
            }

            // Execute stored procedure
            $results = DB::select('EXEC p_AddWorkflowStage2 
                @Order = ?, 
                @StageName = ?, 
                @EscalationLimit = ?, 
                @WorkflowID = ?, 
                @WorkflowTypeID = ?, 
                @WorkflowLimitID = ?, 
                @Count = ?, 
                @StatusID = ?, 
                @CreatedBy = ?, 
                @ModuleID = ?',
                [
                    $nextOrder,
                    $data['StageName'],
                    $data['EscalationLimit'],
                    $data['WorkFlowId'],
                    $data['WorkFlowTypeId'],
                    $data['WorkFlowLimitId'] ?? null,
                    $data['Count'] ?? null,
                    $data['StatusId'] ?? null,
                    $user->Id,
                    $moduleId,
                ]
            );

            $dto = WorkflowStageResult::fromDatabaseResult($results[0] ?? null);

            // Handle errors returned by stored procedure
            if ($dto->isError()) {
                Log::warning('Workflow stage creation failed', [
                    'workflow_id' => $data['WorkFlowId'],
                    'stage_name' => $data['StageName'],
                    'user_id' => $user->id,
                    'message' => $dto->message,
                ]);
                throw new ErroredException($dto->message);
            }

            // Fetch the created stage
            $stage = WorkFlowStage::find($dto->newStageId);
            if (!$stage) {
                throw new ErroredException('Stored procedure did not return a valid stage ID.');
            }

            // If this is marked as final stage, update the workflow using Eloquent
            if ($isFinalStage) {
                $workflow->IsFinalStage = true;
                $workflow->ModifiedBy = $user->Id;
                $workflow->ModifiedOn = now();
                $workflow->save();
                
                // Refresh to get the latest state from database
                $workflow->refresh();
                
                Log::info('Workflow marked as having final stage', [
                    'workflow_id' => $workflow->Id,
                    'stage_id' => $stage->Id,
                    'is_final_stage' => $workflow->IsFinalStage,
                ]);
            }

            // Add IsFinalStage property to stage for response (virtual attribute)
            $stage->IsFinalStage = $isFinalStage;

            // Log activity
            activity()->causedBy($user)
                ->performedOn($stage)
                ->event('create')
                ->log('Created approval workflow stage: ' . $data['StageName'] . ($isFinalStage ? ' (Final Stage)' : ''));

            Log::info('Workflow stage created successfully', [
                'stage_id' => $dto->newStageId,
                'permission_id' => $dto->permissionId,
                'user_id' => $user->id,
                'module_id' => $moduleId,
                'is_final' => $isFinalStage,
                'workflow_has_final' => $workflow->IsFinalStage,
            ]);

            DB::commit();
            
            // Reload stage with relationships
            $stage->load(['type_name', 'workflow']);
            
            return [
                'dto' => $dto,
                'stage' => $stage
            ];

        } catch (ErroredException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Unexpected error creating workflow stage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new ErroredException('An unexpected error occurred while creating the workflow stage.');
        }
    }

    public function deleteStage(int $id): array
    {
        DB::beginTransaction();
        
        try {
            $stage = WorkFlowStage::findOrFail($id);
            $workflowId = $stage->WorkFlowId;
            $workflow = WorkFlow::findOrFail($workflowId);
            $stageName = $stage->StageName;
            
            // Get the highest order (last stage)
            $lastStage = WorkFlowStage::where('WorkFlowId', $workflowId)
                ->orderBy('Order', 'desc')
                ->first();
            
            // Check if we're deleting the last stage (which would be the final one if workflow has final stage)
            $isDeletingLastStage = $lastStage && $lastStage->Id === $id;
            $wasFinalStage = $workflow->IsFinalStage && $isDeletingLastStage;
            
            // Delete the stage
            $stage->delete();
            
            // Get remaining stages count AFTER deletion
            $remainingStages = WorkFlowStage::where('WorkFlowId', $workflowId)->count();
            
            // Reset IsFinalStage flag if we deleted the final stage or no stages remain
            if ($workflow->IsFinalStage && ($wasFinalStage || $remainingStages === 0)) {
                $workflow->IsFinalStage = false;
                $workflow->ModifiedBy = Auth::id();
                $workflow->ModifiedOn = now();
                $workflow->save();
                
                // Refresh to confirm
                $workflow->refresh();
                
                Log::info('Workflow IsFinalStage flag reset after stage deletion', [
                    'workflow_id' => $workflowId,
                    'deleted_stage_id' => $id,
                    'was_last_stage' => $isDeletingLastStage,
                    'remaining_stages' => $remainingStages,
                    'is_final_stage' => $workflow->IsFinalStage,
                ]);
            }

            activity()->performedOn($stage)
                ->event('delete')
                ->log('Deleted approval workflow stage: ' . $stageName . ($wasFinalStage ? ' (Was Final Stage)' : ''));

            DB::commit();
            
            return [
                'success' => true,
                'was_final_stage' => $wasFinalStage,
                'workflow_id' => $workflowId,
                'remaining_stages' => $remainingStages,
            ];
            
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error deleting workflow stage', [
                'stage_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function updateStage(int $id, array $data): WorkFlowStage
    {
        DB::beginTransaction();
        
        try {
            $stage = WorkFlowStage::findOrFail($id);
            $workflow = WorkFlow::findOrFail($stage->WorkFlowId);
            
            // Update the stage with provided data
            $stage->update($data);

            activity()->performedOn($stage)
                ->event('update')
                ->log('Updated approval workflow stage: ' . $stage->StageName);

            DB::commit();
            return $stage;
            
        } catch (ErroredException $e) {
            DB::rollBack();
            throw $e;
            
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error updating workflow stage', [
                'stage_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw new ErroredException('Failed to update workflow stage.');
        }
    }
    
    /**
     * Remove final stage designation from workflow
     */
    public function removeFinalStageDesignation(int $workflowId): bool
    {
        DB::beginTransaction();
        
        try {
            $workflow = WorkFlow::findOrFail($workflowId);
            $workflow->IsFinalStage = false;
            $workflow->ModifiedBy = Auth::id();
            $workflow->ModifiedOn = now();
            $workflow->save();
            
            Log::info('Workflow final stage designation removed', [
                'workflow_id' => $workflowId,
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getStageById(int $id): WorkFlowStage
    {
        return WorkFlowStage::findOrFail($id);
    }
    
    public function getWorkflowStages(int $workflowId): array
    {
        return WorkFlowStage::where('WorkFlowId', $workflowId)
            ->orderBy('Order')
            ->get()
            ->toArray();
    }
    
    /**
     * Check if workflow has a final stage
     */
    public function workflowHasFinalStage(int $workflowId): bool
    {
        $workflow = WorkFlow::find($workflowId);
        return $workflow ? $workflow->IsFinalStage : false;
    }
    
    /**
     * Get which stage is the final one (by checking order)
     */
    public function getFinalStage(int $workflowId): ?WorkFlowStage
    {
        return WorkFlowStage::where('WorkFlowId', $workflowId)
            ->orderBy('Order', 'desc')
            ->first();
    }
}