<?php

namespace App\Http\Requests\Procurement\ProcurementPlan;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;

class SchedulePlanRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function authorize(): bool
    {
        return false;
    }
    public function rules(): array
    {
        return [
            'pending_plan_id' => ['required', 'integer'],
            'ScheduleID'=>['required'],
            'PlanId'=>['required|exists:t_ConsolidatedProcurementPlan,PlanID'],
            'PlanLineId'=>['required|exists:t_PlanLineItem,LineItemmID'],
            'ScheduleQTY'=>['required|decimal'],
            'Status'=> ['string'],
        ];
    }
    public function getPlan(): ConsolidatedProcurementPlan
    {
        $planId = ConsolidatedProcurementPlan::query()->where('id', $this->validated('pending_plan_id'))->with(['lineItems'])->first();
        if ($planId instanceof ConsolidatedProcurementPlan) {
            return $planId;
        }
        throw ValidationException::withMessages(['pending_plan_id' => 'invalid plan selected']);
    }

    public function getQuarterOne(int $lineItemId): int
    {
        return (int) $this->input("q1_{$lineItemId}", 0);
    }

    public function getQuarterTwo(int $lineItemId): int
    {
        return (int) $this->input("q2_{$lineItemId}", 0);
    }

    public function getQuarterThree(int $lineItemId): int
    {
        return (int) $this->input("q3_{$lineItemId}", 0);
    }

    public function getQuarterFour(int $lineItemId): int
    {
        return (int) $this->input("q4_{$lineItemId}", 0);
    }

}
