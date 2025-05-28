<?php

namespace App\Http\Requests\procurement;

use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SchedulePlanRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pending_plan_id' => ['required', 'integer'],
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

    public function getQuaoterTwo(PlanLineItems $item): int
    {
        return 2;
    }

    public function getQuarterOne(PlanLineItems $item): int
    {
        $value = $this->get("q1_{$item->ItemID}");
        //value value is int and return it.
        return 1;
    }
}
