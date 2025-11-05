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
            
            // Create the stage
            $result = $this->stageService->createStage($validated);
            
            // Get the stage with relationships
            $stage = $result['stage'];
            
            // Prepare response data with all necessary relationships
            $stageData = [
                'Id' => $stage->Id,
                'StageName' => $stage->StageName,
                'Order' => $stage->Order,
                'EscalationLimit' => $stage->EscalationLimit,
                'WorkFlowId' => $stage->WorkFlowId,
                'IsFinalStage' => (bool) $stage->IsFinalStage,
                'MaxAmount' => $stage->MaxAmount ?? null,
                'Count' => $stage->Count ?? null,
                'type_name' => $stage->type_name ? [
                    'TypeID' => $stage->type_name->TypeID,
                    'Name' => $stage->type_name->Name,
                ] : null,
                'role_name' => $stage->role_name ?? null,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Workflow stage created successfully' . ($stage->IsFinalStage ? ' (marked as final stage)' : ''),
                'stage' => $stageData,
            ]);

        } catch (\App\Exceptions\ErroredException $e) {
            Log::error('Failed to create workflow stage', [
                'error' => $e->getMessage(),
                // 'request' => $request->all(),
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
                'message' => 'An unexpected error occurred while creating the stage.',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->stageService->deleteStage((int) $id);

            return response()->json([
                'status' => 'success',
                'message' => 'Workflow stage deleted successfully.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to delete workflow stage', [
                'stage_id' => $id,
                'error' => $e->getMessage(),
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
            $validated = $request->validated();
            $stage = $this->stageService->updateStage((int) $id, $validated);

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