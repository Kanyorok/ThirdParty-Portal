<?php

namespace App\Services;

use App\Models\Auth\User;
use App\Models\Core\Approval\WorkflowStage;
use App\Models\Core\Approval\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Settings\WorkFlow;
use Illuminate\Support\Facades\DB;
use App\DTOs\WorkflowStageResult;
use App\Exceptions\ErroredException;
use Illuminate\Support\Facades\Cache;

class WorkFlowStageService
{
    /**
     * CREATE WORKFLOW STAGE
     */
    public function createStage(array $data)
    {
        $user = Auth::user();
        if (!$user) {
            throw new ErroredException('User not authenticated.');
        }

        try {
            $workflow = WorkFlow::findOrFail($data['WorkFlowId']);
            
            // Check if workflow already has a final stage
            if (!empty($workflow->FinalStage)) {
                throw new ErroredException('This workflow already has a final stage. Please remove the final stage designation before adding more stages.');
            }
            
            $nextOrder = WorkflowStage::where('WorkFlowId', $workflow->Id)->max('Order') + 1;

            $moduleId = DB::table('t_ModuleSources')
                ->where('DocumentType', $workflow->Source)
                ->value('ModuleID');

            if (!$moduleId) {
                throw new ErroredException('Module not found for workflow source.');
            }



            $results = DB::select('EXEC p_AddWorkflowStage2 
                @Order = ?, 
                @StageName = ?, 
                @EscalationLimit = ?, 
                @WorkflowID = ?, 
                @WorkflowTypeID = ?, 
                @Count = ?, 
                @StatusID = ?, 
                @CreatedBy = ?, 
                @ModuleID = ?', [
                $nextOrder,
                $data['StageName'],
                $data['EscalationLimit'],
                $data['WorkFlowId'],
                $data['WorkFlowTypeId'],
                $data['Count'] ?? null,
                $data['StatusId'] ?? null,
                $user->Id,
                $moduleId
            ]);



            // Fix for SP leaving transaction open
            try {
                $dbTranCount = DB::select('SELECT @@TRANCOUNT as count')[0]->count;
                $laravelTranCount = DB::transactionLevel();



                while ($dbTranCount > $laravelTranCount) {
                    DB::unprepared('COMMIT TRANSACTION');
                    $tranCountAfter--;
                    Log::info('Committed open transaction from SP');
                } catch (\Exception $e) {
                    Log::warning('Failed to commit SP transaction: ' . $e->getMessage());
                    break;
                }
            }

            Log::info('p_AddWorkflowStage2 Result', ['result' => $results]);

            if (empty($results)) {
                throw new ErroredException('Stored procedure returned no results.');
            }

            $dto = WorkflowStageResult::fromDatabaseResult($results[0]);
            
            if ($dto->isError()) {
                throw new ErroredException($dto->message);
            }

            // Clear all caches before fetching the stage
            $this->clearAllCaches($workflow->Id);

            // Wait a moment for DB to settle
            usleep(100000); // 100ms

            // Fetch the newly created stage with fresh query
            $stage = DB::table('t_WorkflowStages')
                ->where('Id', $dto->newStageId)
                ->first();

            if (!$stage) {
                // Debug: Check if it exists via raw DB
                $rawStage = DB::table('t_WorkflowStages')->where('Id', $dto->newStageId)->first();


                if ($rawStage) {
                    // If found via raw DB but not Eloquent, it's a model issue. 
                    // Try to hydrate manually or investigate model scopes.
                    Log::warning('Stage found via raw DB but not Eloquent. Possible scope or casting issue.');
                    $stage = new WorkflowStage((array)$rawStage);
                    $stage->exists = true;
                } else {
                    throw new ErroredException('Stage creation failed - Record not found after SP execution.');
                }
            }

            // Clear permission cache to ensure the new permission (created by SP) is visible to Spatie
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Handle Permission
            $permission = null;
            
            if (!empty($data['PermissionId'])) {
                $permission = Permission::find($data['PermissionId']);
                if ($permission && $stageModel->PermissionId != $permission->id) {
                    DB::table('t_WorkflowStages')
                        ->where('Id', $stageModel->Id)
                        ->update(['PermissionId' => $permission->id]);
                    $stageModel->PermissionId = $permission->id;
                }
            } else {
                $permission = Permission::find($dto->permissionId);
            }

            if (!$permission) {
                $permissionName = 'workflowstage_' . str_replace(' ', '', $data['StageName']);
                $permission = Permission::where('name', $permissionName)->first();

                if (!$permission) {
                    $permission = Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                        'ModuleId' => $moduleId,
                    ]);
                }

                DB::table('t_WorkflowStages')
                    ->where('Id', $stageModel->Id)
                    ->update(['PermissionId' => $permission->id]);
                $stageModel->PermissionId = $permission->id;
            }

            // Assign permission to user's roles
            $currentUser = Auth::user();
            if ($currentUser) {
                $currentUser->load('roles');
                foreach ($currentUser->roles as $role) {
                    try {
                        if (!$role->hasPermissionTo($permission->id)) {
                            $role->givePermissionTo($permission->id);
                            Log::info("Assigned permission to role", [
                                'permission' => $permission->name,
                                'role' => $role->name
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Could not assign permission: " . $e->getMessage());
                    }
                }
            }

            // Ensure Admin role has the permission
            $adminRole = Role::find(2);
            if ($adminRole && !$adminRole->hasPermissionTo($permission->id)) {
                try {
                    $adminRole->givePermissionTo($permission->id);
                } catch (\Throwable $e) {
                    Log::warning("Could not assign permission to Admin: " . $e->getMessage());
                }
            }

            // Update FinalStage if marked as final
            if (!empty($data['IsFinalStage'])) {
                DB::table('t_Workflows')
                    ->where('Id', $workflow->Id)
                    ->update([
                        'FinalStage' => $stageModel->StageName,
                        'ModifiedBy' => $user->Id,
                        'ModifiedOn' => now()
                    ]);
                
                Log::info('Set final stage', [
                    'workflow_id' => $workflow->Id,
                    'final_stage' => $stageModel->StageName
                ]);
            } else {
                // Update modified timestamp
                DB::table('t_Workflows')
                    ->where('Id', $workflow->Id)
                    ->update([
                        'ModifiedBy' => $user->Id,
                        'ModifiedOn' => now()
                    ]);
            }

            // Clear caches again after all updates
            $this->clearAllCaches($workflow->Id);

            // Reload the stage with relationships
            $stageModel = WorkflowStage::with(['type_name', 'workflow', 'permission.roles'])
                ->find($stageModel->Id);

            Log::info('Workflow stage created successfully', [
                'stage_id' => $stageModel->Id,
                'stage_name' => $stageModel->StageName
            ]);

            return [
                'dto' => $dto,
                'stage' => $stageModel,
                'permission' => $permission,
            ];
            
        } catch (ErroredException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Workflow stage creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new ErroredException('Error creating workflow stage: ' . $e->getMessage());
        }
    }

    /**
     * DELETE WORKFLOW STAGE
     */
    public function deleteStage(int $id): array
    {
        try {
            $stage = WorkflowStage::findOrFail($id);
            $workflow = WorkFlow::findOrFail($stage->WorkFlowId);

            $stageName = $stage->StageName;
            $workflowId = $workflow->Id;
            $wasFinalStage = ($stage->StageName === $workflow->FinalStage);

            Log::info('Deleting workflow stage', [
                'stage_id' => $id,
                'stage_name' => $stageName,
                'was_final' => $wasFinalStage,
                'workflow_id' => $workflowId
            ]);

            // Delete the stage
            DB::table('t_WorkflowStages')
                ->where('Id', $id)
                ->delete();

            // Recalculate FinalStage
            $remainingStages = DB::table('t_WorkflowStages')
                ->where('WorkFlowId', $workflowId)
                ->orderBy('Order', 'desc')
                ->get();

            $newFinalStage = null;
            
            if ($remainingStages->isNotEmpty()) {
                // If the deleted stage was final, don't automatically set a new final stage
                // Let the user explicitly mark another stage as final
                if (!$wasFinalStage && !empty($workflow->FinalStage)) {
                    // Keep existing final stage if it wasn't the deleted one
                    $newFinalStage = $workflow->FinalStage;
                }
            }

            // Update workflow
            DB::table('t_Workflows')
                ->where('Id', $workflowId)
                ->update([
                    'FinalStage' => $newFinalStage,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now()
                ]);

            // Clear caches
            $this->clearAllCaches($workflowId);

            activity()
                ->performedOn($stage)
                ->event('delete')
                ->log("Deleted workflow stage: {$stageName}");

            Log::info('Stage deleted successfully', [
                'stage_id' => $id,
                'new_final_stage' => $newFinalStage
            ]);

            return [
                'success' => true,
                'final_stage' => $newFinalStage,
                'was_final_stage' => $wasFinalStage
            ];
            
        } catch (\Throwable $e) {
            Log::error('Failed to delete workflow stage', [
                'stage_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * UPDATE STAGE
     */
    public function updateStage(int $id, array $data): WorkFlowStage
    {
        try {
            $stage = WorkflowStage::findOrFail($id);
            
            DB::table('t_WorkflowStages')
                ->where('Id', $id)
                ->update(array_merge($data, [
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now()
                ]));

            // Clear caches
            $this->clearAllCaches($stage->WorkFlowId);

            activity()
                ->performedOn($stage)
                ->event('update')
                ->log("Updated workflow stage: {$stage->StageName}");

            return WorkflowStage::find($id);
            
        } catch (\Throwable $e) {
            Log::error('Failed to update workflow stage', [
                'stage_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw new ErroredException('Failed to update workflow stage: ' . $e->getMessage());
        }
    }

    /**
     * GET FINAL STAGE (highest Order)
     */
    public function getFinalStage(int $workflowId): ?WorkFlowStage
    {
        return WorkflowStage::where('WorkFlowId', $workflowId)
            ->orderBy('Order', 'desc')
            ->first();
    }

    /**
     * Clear all relevant caches
     */
    private function clearAllCaches(int $workflowId): void
    {
        try {
            // Clear Laravel cache
            Cache::forget("workflow_{$workflowId}");
            Cache::forget("workflow_stages_{$workflowId}");
            
            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            // Clear query cache if using it
            if (method_exists(DB::class, 'flushQueryCache')) {
                DB::flushQueryCache();
            }
            
            Log::info('Cleared all caches for workflow', ['workflow_id' => $workflowId]);
        } catch (\Exception $e) {
            Log::warning('Failed to clear some caches: ' . $e->getMessage());
        }
    }
}