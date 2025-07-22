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
            'Property' => 'required|string|max:50',
            'Block' => 'required|string|max:50',
            'Floor' => 'required|string|max:50',
            'Unit' => 'required|string|max:50',
            'CompletionDate' => 'required|date',
            'WorkDoneSummary' => 'required|string|max:255',
            'PartsUsed' => 'required|string|max:255',
            'Cost' => 'required|integer',
            'FinalStatus' => 'required|exists:t_CodeDetails,ID',
        ];
    }
}
