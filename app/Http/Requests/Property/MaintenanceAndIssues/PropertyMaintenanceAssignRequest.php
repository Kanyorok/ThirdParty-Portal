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
            'Property' => 'required|string|max:100',
            'Block' => 'required|string|max:100',
            'Floor' => 'required|string|max:100',
            'Unit' => 'required|string|max:100',
            'AssignmentDate' => 'required|date',
            'AssignmentType' => 'required|exists:t_CodeDetails,Id',
            'InternalTechnician' => 'nullable|exists:t_Employees,Id',
            'PrequalifiedVendor' => 'nullable|exists:t_Suppliers,Id',
            'ExpectedStartDate' => 'required|date',
            'ExpectedCompletion' => 'required|date',
            'PriorityLevel' => 'required|exists:t_CodeDetails,Id',
            'InstructionNotes' => 'nullable|string|max:100',
        ];
    }
}
