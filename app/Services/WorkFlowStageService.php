<?php

namespace App\Services;

use App\Models\Auth\User;
use App\Models\Core\Approval\WorkFlowStage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Settings\WorkFlow;
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
            // Fetch workflow
            $workflow = WorkFlow::findOrFail($data['WorkFlowId']);

            // Validate final stage logic
            $isFinalStage = !empty($data['IsFinalStage']) && $data['IsFinalStage'];
            
            if ($isFinalStage) {
                // Check if workflow already has a final stage
                if ($workflow->IsFinalStage) {
                    throw new ErroredException('This workflow already has a final stage. Please remove the existing final stage before adding a new one.');
                }
            }

            // Compute next order
            $nextOrder = WorkFlowStage::where('WorkFlowId', $data['WorkFlowId'])->max('Order') + 1;

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
                    $data['Count'],
                    $data['StatusId'] ?? null,
                    $user->Id,
                    property_exists($workflow, 'ModuleId') ? $workflow->ModuleId : null,
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

            // If this is marked as final stage, update the workflow flag
            if ($isFinalStage) {
                $workflow->update(['IsFinalStage' => true]);
                
                // Optionally store which stage is the final one (if you need to track it)
                // You could add a field or use naming convention
            }

            // Log activity
            activity()->causedBy($user)
                ->performedOn($stage)
                ->event('create')
                ->log('Created approval workflow stage: ' . $data['StageName'] . ($isFinalStage ? ' (Final Stage)' : ''));

            Log::info('Workflow stage created successfully', [
                'stage_id' => $dto->newStageId,
                'permission_id' => $dto->permissionId,
                'user_id' => $user->id,
                'is_final' => $isFinalStage,
            ]);

            DB::commit();
            return $dto;

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

    public function deleteStage(int $id): bool
    {
        DB::beginTransaction();
        
        try {
            $stage = WorkFlowStage::findOrFail($id);
            $workflow = WorkFlow::findOrFail($stage->WorkFlowId);
            
            // Check if this is the final stage by checking if it's the last one in order
            // Or you can add a marker field to identify which stage is final
            $isLastStage = WorkFlowStage::where('WorkFlowId', $stage->WorkFlowId)
                ->where('Order', '>', $stage->Order)
                ->count() === 0;
            
            // Delete the stage
            $stageName = $stage->StageName;
            $stage->delete();

            // If workflow had final stage flag set and this was potentially the final stage
            // Check if there are any remaining stages, if not, reset the flag
            $remainingStages = WorkFlowStage::where('WorkFlowId', $workflow->Id)->count();
            
            if ($remainingStages === 0 && $workflow->IsFinalStage) {
                $workflow->update(['IsFinalStage' => false]);
                
                Log::info('Last stage deleted, workflow IsFinalStage flag reset', [
                    'workflow_id' => $workflow->Id,
                    'stage_id' => $id,
                ]);
            }

            activity()->performedOn($stage)
                ->event('delete')
                ->log('Deleted approval workflow stage: ' . $stageName);

            DB::commit();
            return true;
            
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error deleting workflow stage', [
                'stage_id' => $id,
                'error' => $e->getMessage(),
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
     * Mark a specific stage as the final stage for the workflow
     */
    public function markStageAsFinal(int $stageId): bool
    {
        DB::beginTransaction();
        
        try {
            $stage = WorkFlowStage::findOrFail($stageId);
            $workflow = WorkFlow::findOrFail($stage->WorkFlowId);
            
            // Check if workflow already has a final stage
            if ($workflow->IsFinalStage) {
                throw new ErroredException('This workflow already has a final stage.');
            }
            
            // Mark workflow as having a final stage
            $workflow->update(['IsFinalStage' => true]);
            
            Log::info('Workflow stage marked as final', [
                'workflow_id' => $workflow->Id,
                'stage_id' => $stageId,
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
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
            $workflow->update(['IsFinalStage' => false]);
            
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
        return $workflow ? (bool) $workflow->IsFinalStage : false;
    }
}