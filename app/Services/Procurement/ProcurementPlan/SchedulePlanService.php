<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\Procurement\SchedulePlanEnum;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItem;
use App\Models\Procurement\SchedulePlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;

class SchedulePlanService
{
    public function create(array $data, User $actor, ConsolidatedProcurementPlan $plan, PlanLineItem $lineItem): SchedulePlan
    {
        Validator::make($data, [
            'ScheduleQTY' => 'required|integer|min:0',
            'ScheduleType' => ['required', 'string', Rule::in(['month', 'quarter'])],
            'Status' => ['required', new Enum(SchedulePlanEnum::class)],
            'periods' => 'required|array',
            'periods.*' => 'nullable|integer|min:0',
        ])->validate();

        return DB::transaction(function () use ($data, $actor, $plan, $lineItem) {
            $schedule = SchedulePlan::updateOrCreate(
                [
                    'PlanId' => $plan->PlanID,
                    'PlanLineId' => $lineItem->LineItemID,
                ],
                [
                    'ScheduleQTY' => $data['ScheduleQTY'],
                    'ScheduleType' => $data['ScheduleType'],
                    'Status' => $data['Status']->value,
                    'ScheduleId' => 'Scheduled-' . Str::upper(Str::random(5)),
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]
            );

            // Delete all old periods associated with this schedule
            $schedule->periods()->delete();
            $validPeriods = array_filter($data['periods'], fn($qty) => (int)$qty > 0);

            foreach ($validPeriods as $period => $qty) {
                $schedule->periods()->create([
                    'SchedulePeriod' => $period,
                    'ScheduleQTY' => (int)$qty,
                    'CreatedBy' => $actor->Id,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $actor->Id,
                    'ModifiedOn' => now(),
                ]);
            }
            return $schedule;
        });
    }
}
