<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\ProcurementPlanStatusEnum;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;

class SubmitPlanService
{
    public function __construct(public ConsolidatedProcurementPlan $consolidatedProcurementPlan) {}

    public function submit(User $actor): static
    {
        // Update plan status to Submitted
        $this->consolidatedProcurementPlan->forceFill([
            'Status' => ProcurementPlanStatusEnum::Submitted->value,
        ])->save(['timestamps' => false]);

        return $this;
    }
}
