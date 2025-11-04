<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Settings\WorkFlowStageRequest;
use App\Services\WorkFlowStageService;
use App\Exceptions\ErroredException;

class WorkflowStagesController extends Controller
{
    protected $stageService;

    public function __construct(WorkFlowStageService $stageService)
    {
        $this->stageService = $stageService;
    }

    public function store(WorkFlowStageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->stageService->createStage($validated);

            return response()->json([
                'status' => 'success',
                'message' => $result->message,
                'newStageId' => $result->newStageId,
                'permissionId' => $result->permissionId,
            ], 201);

        } catch (ErroredException $e) {
            // Custom app-level error
            return $e->toJson();

        } catch (\Throwable $e) {
            // Unexpected or system-level error
            Log::error('Workflow stage creation fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return (new ErroredException('Internal server error while creating workflow stage.'))
                ->toJson();
        }
    }

    public function destroy($id)
    {
        try {
            $this->stageService->deleteStage($id);
            return redirect()->back()->with('success', 'Approval workflow stage deleted.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete approval stage: ' . $e->getMessage()]);
        }
    }
}
