<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Settings\WorkFlowStageRequest;
use App\Services\WorkFlowStageService;

class WorkflowStagesController extends Controller
{
    protected $stageService;

    public function __construct(WorkFlowStageService $stageService)
    {
        $this->stageService = $stageService;
    }

    public function store(WorkFlowStageRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->stageService->createStage($validated);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create approval stage: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Approval workflow stage created.');
    }

    public function destroy($id)
    {
        try {
            $this->stageService->deleteStage($id);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete approval stage: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Approval workflow stage deleted.');
    }
}
