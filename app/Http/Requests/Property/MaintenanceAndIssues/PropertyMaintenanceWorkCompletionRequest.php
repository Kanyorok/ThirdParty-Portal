<?php

namespace App\Http\Requests\Property\MaintenanceAndIssues;

use Illuminate\Foundation\Http\FormRequest;

class PropertyMaintenanceWorkCompletionRequest extends FormRequest
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
            'RequestNumber' => 'required|exists:t_AssignRequest,Id',
            'CompletionDate' => 'required|date|before_or_equal:today',
            'WorkDoneSummary' => 'required|string|max:255',
            'PartsUsed' => 'nullable|string|max:255',
            'Cost' => 'nullable|integer',
            'FinalStatus' => 'required|exists:t_CodeDetails,ID',
            'Document' => 'nullable|array',
            'Document.*' => 'file|max:25000',
        ];
    }
}
