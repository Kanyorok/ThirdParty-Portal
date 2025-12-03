<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Services\Procurement\ProcurementPlan\ProcurementPlanWorkflow;

class SubmitPlanService
{
    public function __construct(public ConsolidatedProcurementPlan $consolidatedProcurementPlan) {}

    public function submit(User $actor): static
    {
        // Initialize workflow
        /** @var ProcurementPlanWorkflow $workflow */
        $workflow = app(ProcurementPlanWorkflow::class);
        $workflow->submit($this->consolidatedProcurementPlan, $actor);

        // Update plan status to Submitted
        $this->consolidatedProcurementPlan->forceFill([
            'Status' => ProcurementPlanStatusEnum::Submitted,
        ])->save();

        return $this;
    }
}
