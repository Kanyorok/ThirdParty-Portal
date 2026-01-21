<?php

namespace App\Http\Requests\Property\MaintenanceAndIssues;

use Illuminate\Foundation\Http\FormRequest;

class PropertyMaintenanceAssignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'RequestNumber' => 'required|exists:t_MaintenanceRequest,Id',
            'AssignmentDate' => 'required|date|after_or_equal:today|before_or_equal:ExpectedCompletion',
            'AssignmentType' => 'required|exists:t_CodeDetails,Id',

            'InternalTechnician' => 'nullable|exists:t_Employees,Id|required_without:PrequalifiedVendor',
            'PrequalifiedVendor' => 'nullable|exists:t_SupplierMaster,Id|required_without:InternalTechnician',

            'ExpectedStartDate' => 'required|date|after_or_equal:today',
            'ExpectedCompletion' => 'required|date|after_or_equal:ExpectedStartDate',
            'PriorityLevel' => 'required|exists:t_CodeDetails,Id',
            'InstructionNotes' => 'required|string|max:100',
        ];
    }


    public function messages()
    {
        return [
            'ExpectedStartDate.after_or_equal' => 'Expected start date must be today or a future date.',
            'ExpectedCompletion.after_or_equal' => 'Expected completion must be after or equal to the expected start date.',
            'AssignmentDate.after_or_equal' => 'Assignment date must be today or later.',
            'AssignmentDate.before_or_equal' => 'Assignment date must be before or equal to the expected completion date.',
            'InternalTechnician.required_without' => 'Please assign either an internal technician or a prequalified vendor.',
            'PrequalifiedVendor.required_without' => 'Please assign either an internal technician or a prequalified vendor.',
        ];
    }

}
