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
            'AssignmentDate' => 'required|date|after_or_equal:today|before:ExpectedCompletion',
            'AssignmentType' => 'required|exists:t_CodeDetails,Id',
            'InternalTechnician' => 'nullable|exists:t_Employees,Id',
            'PrequalifiedVendor' => 'nullable|exists:t_Suppliers,Id',
            'ExpectedStartDate' => 'required|date|after_or_equal:today',
            'ExpectedCompletion' => 'required|date|after:ExpectedStartDate',
            'PriorityLevel' => 'required|exists:t_CodeDetails,Id',
            'InstructionNotes' => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'ExpectedStartDate.after_or_equal' => 'Expected start date must be today or a future date.',
            'ExpectedCompletion.after' => 'Expected completion must be after the expected start date.',
            'AssignmentDate.after_or_equal' => 'Assignment date must be today or later.',
            'AssignmentDate.before' => 'Assignment date must be before the expected completion date.',
        ];
    }

}
