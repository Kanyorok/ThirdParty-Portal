<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\WorkFlowStageRequest;
use App\Services\WorkFlowStageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WorkflowStagesController extends Controller
{
    protected $stageService;

    public function __construct(WorkFlowStageService $stageService)
    {
        $this->stageService = $stageService;
    }

    public function store(WorkFlowStageRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->authorize('update', \App\Models\Settings\WorkFlow::findOrFail($validated['WorkFlowId']));

            Log::info('Storing new workflow stage', [
                'workflow_id' => $validated['WorkFlowId'],
                'stage_name' => $validated['StageName'],
                'is_final' => ! empty($validated['IsFinalStage']),
            ]);

            // Create the stage
            $result = $this->stageService->createStage($validated);

            // Get the stage with relationships - force fresh query
            $stage = \App\Models\Core\Approval\WorkflowStage::with(['type_name', 'workflow', 'permission.roles'])
                ->find($result['stage']->Id);

            if (! $stage) {
                throw new \Exception('Stage created but could not be retrieved');
            }

            $permission = $result['permission'] ?? null;

            // Get workflow to check final stage status
            $workflow = \App\Models\Settings\WorkFlow::find($validated['WorkFlowId']);
            $workflow->refresh(); // Force refresh from database

            $isFinalStage = ($stage->StageName === $workflow->FinalStage);

            // Prepare response data with all necessary relationships
            $stageData = [
                'Id' => $stage->Id,
                'StageName' => $stage->StageName,
                'Order' => $stage->Order,
                'EscalationLimit' => $stage->EscalationLimit,
                'WorkFlowId' => $stage->WorkFlowId,
                'IsFinalStage' => $isFinalStage,
                'MaxAmount' => $stage->MaxAmount ?? null,
                'Count' => $stage->Count ?? null,
                'type' => $stage->type_name ? [
                    'TypeID' => $stage->type_name->TypeID,
                    'Name' => $stage->type_name->Name,
                ] : null,
                'role_name' => $permission && $permission->roles ?
                    $permission->roles->pluck('name')->implode(', ') : '-',
            ];

            Log::info('Workflow stage created successfully', [
                'stage_id' => $stage->Id,
                'is_final' => $isFinalStage,
                'workflow_has_final' => ! empty($workflow->FinalStage),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Workflow stage created successfully'
                    . ($isFinalStage ? ' (final stage)' : ''),
                'stage' => $stageData,
                'workflow_has_final_stage' => ! empty($workflow->FinalStage),
            ]);
        } catch (\App\Exceptions\ErroredException $e) {
            Log::error('Failed to create workflow stage', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Unexpected error in workflow stage creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $stage = \App\Models\Core\Approval\WorkflowStage::findOrFail($id);
            $this->authorize('update', \App\Models\Settings\WorkFlow::findOrFail($stage->WorkFlowId));

            Log::info('Attempting to delete workflow stage', ['stage_id' => $id]);

            // Get stage info before deletion for logging
            $stage = \App\Models\Core\Approval\WorkflowStage::find($id);
            if (! $stage) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stage not found.',
                ], 404);
            }

            $workflowId = $stage->WorkFlowId;
            $stageName = $stage->StageName;

            // Call service and get the result
            $result = $this->stageService->deleteStage((int) $id);

            // Fetch updated workflow state
            $workflow = \App\Models\Settings\WorkFlow::find($workflowId);
            $workflow->refresh();

            Log::info('Stage deleted successfully', [
                'stage_id' => $id,
                'stage_name' => $stageName,
                'was_final' => $result['was_final_stage'],
                'new_final' => $result['final_stage'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Workflow stage deleted successfully.',
                'final_stage' => $result['final_stage'],
                'was_final_stage' => $result['was_final_stage'],
                'workflow_has_final_stage' => ! empty($workflow->FinalStage),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete workflow stage', [
                'stage_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete stage: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(WorkFlowStageRequest $request, string $id): JsonResponse
    {
        try {
            $stage = \App\Models\Core\Approval\WorkflowStage::findOrFail($id);
            $this->authorize('update', \App\Models\Settings\WorkFlow::findOrFail($stage->WorkFlowId));

            $validated = $request->validated();
            $stage = $this->stageService->updateStage((int) $id, $validated);

            // Reload with relationships
            $stage = \App\Models\Core\Approval\WorkflowStage::with(['type_name', 'workflow', 'permission.roles'])
                ->find($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Workflow stage updated successfully.',
                'stage' => $stage,
            ]);
        } catch (\App\Exceptions\ErroredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to update workflow stage', [
                'stage_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update stage.',
            ], 500);
        }
    }
}
