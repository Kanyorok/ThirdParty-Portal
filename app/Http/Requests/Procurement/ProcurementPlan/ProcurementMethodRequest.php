<?php

namespace App\Http\Requests\Procurement\ProcurementPlan;

use Illuminate\Foundation\Http\FormRequest;

class ProcurementMethodRequest extends FormRequest
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
            'MethodId'=>'required',
            'ApprovedPlanId'=>'required|exists:t_ConsolidatedProcurementPlan,PlanID',
            'ApprovedPlanLineId'=>'required|exists:t_PlanLineItem,LineItemmID',
            'AssignedMethod'=>'required|string',
            'Justification'=> 'text',
        ];
    }
}
