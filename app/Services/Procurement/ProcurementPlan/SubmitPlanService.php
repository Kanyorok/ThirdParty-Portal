<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;

class SubmitPlanService
{
    public function __construct(public ConsolidatedProcurementPlan $consolidatedProcurementPlan)
    {
    }

    public function submit(User $actor): static
    {
        // Update plan status to Submitted
        $this->consolidatedProcurementPlan->forceFill([
            'Status' => ProcurementPlanStatusEnum::Submitted->value,
        ])->save(['timestamps' => false]);

        // Create workflow record for the submission
        $this->consolidatedProcurementPlan->workflows()->create([
            'Stage' => ProcurementPlanStatusEnum::Submitted->name,
            'Status' => WorkflowStatus::Submitted->value,
            'Notes' => 'Plan Submission',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        // Log the activity (optional)
        activity()
            ->causedBy($actor)
            ->performedOn($this->consolidatedProcurementPlan)
            ->event('submit')
            ->log('Submitted plan ID ' . $this->consolidatedProcurementPlan->PlanID . ' for approval.');

        return $this;
    }
}
