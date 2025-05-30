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
        return true;
    }

    public function rules(): array
    {
        return [

            'pending_plan_id' => 'required|integer|exists:t_ConsolidatedProcurementPlan,PlanID',
            'lineItemIds' => 'required|array',
            'lineItemIds.*' => 'required|integer|exists:t_PlanLineItem,LineItemID',
        ];
    }

    public function getPlan(): ConsolidatedProcurementPlan
    {
        $plan = ConsolidatedProcurementPlan::query()
            ->where('PlanID', $this->validated('pending_plan_id'))
            ->with(['lineItems'])
            ->first();

        if ($plan instanceof ConsolidatedProcurementPlan) {
            return $plan;
        }

        throw ValidationException::withMessages(['pending_plan_id' => 'Invalid plan selected']);
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
