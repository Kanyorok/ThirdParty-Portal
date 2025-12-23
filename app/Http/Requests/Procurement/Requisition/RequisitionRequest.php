<?php

namespace App\Http\Requests\Procurement\Requisition;

use Illuminate\Foundation\Http\FormRequest;

class RequisitionRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'ProcurementPlan' => ['required',  'string'],
            'Branch' => ['required', 'numeric'],
            'Department' => ['required', 'numeric'],
            'Remarks' => ['required', 'string'],

            //
               ];
    }

    public function messages(): array
    {
        return [
            'ProcurementPlan.required' => 'Please select a Procurement Plan.',
            'Branch.required'          => 'The Branch field is required.',
            'Department.required'      => 'The Department field is required.',
            'Remarks.required'         => 'Please provide remarks or a description.',
        ];
    }
}
