<?php

namespace App\Http\Requests\Procurement\ProcurementPlan;

use Illuminate\Foundation\Http\FormRequest;

class SchedulePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }
    public function rules(): array
    {
        return [
            'ScheduleID'=>'required',
            'PlanId'=>'required|exists:t_ConsolidatedProcurementPlan,PlanID',
            'PlanLineId'=>'required|exists:t_PlanLineItem,LineItemmID',
            'ScheduleQTY'=>'required|decimal',
            'Status'=> 'string',
        ];
    }
}
