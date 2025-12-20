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

        DB::beginTransaction();
        try {
            $workflow = WorkFlow::findOrFail($data['WorkFlowId']);
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
                    $dbTranCount = DB::select('SELECT @@TRANCOUNT as count')[0]->count;
                    Log::warning('Fixed mismatched transaction count from SP (Forced COMMIT)');
                }
            } catch (\Throwable $e) {
                Log::warning('Could not check/fix transaction count: ' . $e->getMessage());
            }

            $dto = WorkflowStageResult::fromDatabaseResult($results[0] ?? null);
            if ($dto->isError()) {
                throw new ErroredException($dto->message);
            }

            $stage = WorkflowStage::find($dto->newStageId);

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

            // === Handle Permission ===
            // If user selected a permission, use it. Otherwise fall back to SP result or auto-creation.
            if (!empty($data['PermissionId'])) {
                $permission = \App\Models\Core\Approval\Permission::find($data['PermissionId']);
                if ($permission) {
                    $stage->PermissionId = $permission->id;
                    $stage->save();
                }
            } else {
                // Fallback: use permission from SP result
                $permission = \App\Models\Core\Approval\Permission::find($dto->permissionId);
            }

            if (!$permission) {
                // Fallback: try to find by name if ID lookup fails (shouldn't happen)
                $permissionName = 'workflowstage_' . str_replace(' ', '', $data['StageName']);
                $permission = \App\Models\Core\Approval\Permission::where('name', $permissionName)->first();

                if (!$permission) {
                    // Last resort: create it (though SP should have done it)
                    $permission = \App\Models\Core\Approval\Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                        'ModuleId' => $moduleId,
                    ]);
                }

                // Ensure stage is linked to this new/found permission
                $stage->PermissionId = $permission->id;
                $stage->save();
            }

            // Assign permission to the creator's roles
            /** @var \App\Models\Auth\User $currentUser */
            $currentUser = Auth::user();
            if ($currentUser) {
                $currentUser->load('roles'); // Eager load roles if not already
                foreach ($currentUser->roles as $role) {
                    try {
                        $role->givePermissionTo($permission->id);
                    } catch (\Throwable $e) {
                        // Ignore if already exists or other minor issues
                        Log::warning("Could not assign permission {$permission->name} to role {$role->name}: " . $e->getMessage());
                    }
                }
            }

            // Also ensure Admin (Role 2) has it as a fallback/standard
            $adminRole = Role::find(2);
            if ($adminRole) {
                try {
                    $adminRole->givePermissionTo($permission->id);
                } catch (\Throwable $e) {
                    // Ignore
                    Log::warning("Could not assign permission {$permission->name} to Admin role: " . $e->getMessage());
                }
            }

            // Update FinalStage logic
            if (!empty($data['IsFinalStage'])) {
                $workflow->FinalStage = $stage->StageName;
            }

            $workflow->ModifiedBy = $user->Id;
            $workflow->ModifiedOn = now();
            $workflow->save();

            DB::commit();

            $stage->load(['type_name', 'workflow']);

            return [
                'dto' => $dto,
                'stage' => $stage,
                'permission' => $permission, // return permission for front-end reference
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Workflow stage creation error', [$e->getMessage()]);
            throw new ErroredException('Error creating workflow stage.');
        }
    }

    /**
     * DELETE WORKFLOW STAGE
     */
    public function deleteStage(int $id): array
    {
        DB::beginTransaction();

        try {
            $stage = WorkflowStage::findOrFail($id);
            $workflow = WorkFlow::findOrFail($stage->WorkFlowId);

            $stageName = $stage->StageName;
            $workflowId = $workflow->Id;
            $wasFinalStage = ($stage->StageName === $workflow->FinalStage); // Check if this was the final stage

            $stage->delete();

            // Only recalculate FinalStage if the deleted stage was the final one
            if ($wasFinalStage) {
                $newFinal = WorkflowStage::where('WorkFlowId', $workflowId)
                    ->orderBy('Order', 'desc')
                    ->first();

                $workflow->FinalStage = $newFinal ? $newFinal->StageName : null;
            }
            // If it wasn't final, leave FinalStage unchanged

            $workflow->ModifiedBy = Auth::id();
            $workflow->ModifiedOn = now();
            $workflow->save();

            activity()
                ->performedOn($stage)
                ->event('delete')
                ->log("Deleted workflow stage: {$stageName}");

            DB::commit();

            return [
                'success' => true,
                'final_stage' => $workflow->FinalStage
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * UPDATE STAGE
     */
    public function updateStage(int $id, array $data): WorkFlowStage
    {
        DB::beginTransaction();

        try {
            $stage = WorkflowStage::findOrFail($id);
            $stage->update($data);

            activity()
                ->performedOn($stage)
                ->event('update')
                ->log("Updated workflow stage: {$stage->StageName}");

            DB::commit();
            return $stage;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new ErroredException('Failed to update workflow stage.');
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
}
