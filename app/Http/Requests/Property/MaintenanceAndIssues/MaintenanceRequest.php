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
            'Property' => 'required|exists:t_PropertyRegistry,id',
            'Block' => 'required|exists:t_PropertyBlock,id',
            'Floor' => 'required|exists:t_PropertyFloor,id',
            'Unit' => 'required|exists:t_PropertyUnit,id',
            'ReportedBy' => 'required|string|max:50',
            'IssueType' => 'required|string|max:50',
            'Priority' => 'required|string|max:50',
            'IssueDescription' => 'required|string|max:255',        ];
    }
}
