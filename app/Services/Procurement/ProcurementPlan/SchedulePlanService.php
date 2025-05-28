<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;
use App\Models\Procurement\SchedulePlan;
use Illuminate\Support\Str;
use App\Models\Procurement\SchedulePeriod;
use Carbon\Carbon;


class SchedulePlanService
{

    public function create(array $data, User $actor, ConsolidatedProcurementPlan $plan, PlanLineItems $lineItem): SchedulePlan
{
    $existing = SchedulePlan::where('PlanId', $plan->PlanID)
        ->where('PlanLineId', $lineItem->LineItemID)
        ->first();

    $schedule = $existing
        ? tap($existing)->update([
            'ScheduleQTY' => $data['ScheduleQTY'],
            'Status' => $data['Status']->value, 
            'ModifiedBy' => $actor->Id,
        ])
        : SchedulePlan::create([
            'ScheduleId' => 'Scheduled-' . Str::upper(Str::random(5)),
            'PlanId' => $plan->PlanID,
            'PlanLineId' => $lineItem->LineItemID,
            'ScheduleQTY' => $data['ScheduleQTY'],
            'Status' => $data['Status']->value, 
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

    
    SchedulePeriod::where('ScheduleId', $schedule->Id)->delete();

    $now = Carbon::now();

    foreach ($data['periods'] as $period => $qty) {
        if ((int) $qty > 0) {
            SchedulePeriod::create([
                'ScheduleId' => $schedule->Id,
                'SchedulePeriod' => $period,
                'ScheduleQTY' => (int) $qty,
                'CreatedBy' => $actor->Id,
                'CreatedOn' => $now,
                'ModifiedBy' => $actor->Id,
                'ModifiedOn' => $now,
            ]);
        }
    }

    return $schedule;
}


}
