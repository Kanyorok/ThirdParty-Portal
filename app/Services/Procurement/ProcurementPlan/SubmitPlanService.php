<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use Illuminate\Support\Facades\Log;

class SubmitPlanService
{
    public function __construct(public ConsolidatedProcurementPlan $plan)
    {
         $this->plan = $plan;
    }

    public function submit(User $actor, string $remarks = 'Submitted for approval'): bool
    {
        Log::info("SubmitPlanService: Submitting plan", [
            'planId' => $this->plan->PlanId,
            'actorId' => $actor->Id,
        ]);

        // Use the workflow service to handle submission
        $workflowService = new ConsolidatedPlanWorkflowService($this->plan);
        
        return $workflowService->submitForApproval($actor, $remarks);
    }
}
