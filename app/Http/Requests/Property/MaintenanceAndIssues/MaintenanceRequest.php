<?php

namespace App\Http\Requests\Property\MaintenanceAndIssues;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceRequest extends FormRequest
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
            'Property' => 'required|exists:t_PropertyRegistry,Id',
            'Block' => 'nullable|exists:t_PropertyBlock,Id',
            'Floor' => 'nullable|exists:t_PropertyFloor,Id',
            'Unit' => 'nullable|exists:t_PropertyUnit,Id',
            'ReportedBy' => 'required|string|max:50',
            'IssueType' => 'required|exists:t_CodeDetails,ID',
            'Priority' => 'required|exists:t_CodeDetails,ID',
            'IssueDescription' => 'required|string|max:255',
            'Document' => 'nullable|array',
            'Document.*' => 'file|max:9000',
        ];
    }

}
