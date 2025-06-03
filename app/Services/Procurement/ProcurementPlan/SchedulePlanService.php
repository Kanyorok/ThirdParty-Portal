<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\Procurement\SchedulePlanEnum;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;
use App\Models\Procurement\SchedulePlan;
use Illuminate\Support\Str;
use App\Models\Procurement\SchedulePeriod;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;


class SchedulePlanService
{

    public function create(array $data, User $actor, ConsolidatedProcurementPlan $plan, PlanLineItems $lineItem): SchedulePlan
    {
        Validator::make($data, [
            'ScheduleQTY' => 'required|integer|min:0',
            'Status' => ['required', new Enum(SchedulePlanEnum::class)],
            'periods' => 'required|array',
            'periods.*' => 'nullable|integer|min:0',
        ])->validate();

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

        // Clear existing period records if re-scheduling
        SchedulePeriod::where('ScheduleId', $schedule->Id)->delete();

        $now = Carbon::now();

        foreach ($data['periods'] as $period => $qty) {
            if ((int)$qty > 0) {
                SchedulePeriod::create([
                    'ScheduleId' => $schedule->Id,
                    'SchedulePeriod' => $period,
                    'ScheduleQTY' => (int)$qty,
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
