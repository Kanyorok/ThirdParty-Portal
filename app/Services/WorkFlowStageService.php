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
        $nextOrder = WorkFlowStage::where('WorkFlowId', $workflow->Id)->max('Order') + 1;

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

        $dto = WorkflowStageResult::fromDatabaseResult($results[0] ?? null);
        if ($dto->isError()) {
            throw new ErroredException($dto->message);
        }

        $stage = WorkFlowStage::find($dto->newStageId);
        if (!$stage) {
            throw new ErroredException('Stage creation failed.');
        }

        // === Create Permission for this Stage ===
        $permissionName = 'workflow-stage-' . $stage->Id;
        $permission = \App\Models\Core\Approval\Permission::firstOrCreate(
            ['name' => $permissionName],
            [
                'guard_name' => 'web',
                'ModuleId' => $moduleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Optional: Attach permission to default role(s)
        // $defaultRoleId = 1; // admin role
        // DB::table('t_RolePermissions')->updateOrInsert(
        //     ['role_id' => $defaultRoleId, 'permission_id' => $permission->id]
        // );

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
            $stage = WorkFlowStage::findOrFail($id);
            $workflow = WorkFlow::findOrFail($stage->WorkFlowId);

            $stageName = $stage->StageName;
            $workflowId = $workflow->Id;
            $wasFinalStage = ($stage->StageName === $workflow->FinalStage); // Check if this was the final stage

            $stage->delete();

            // Only recalculate FinalStage if the deleted stage was the final one
            if ($wasFinalStage) {
                $newFinal = WorkFlowStage::where('WorkFlowId', $workflowId)
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
            $stage = WorkFlowStage::findOrFail($id);
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
        return WorkFlowStage::where('WorkFlowId', $workflowId)
            ->orderBy('Order', 'desc')
            ->first();
    }
}

