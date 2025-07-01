<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyLeaseTerminationRequest extends FormRequest
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
            'LeaseID' => 'required|exists:t_LeaseCreation,Id',
            'TerminationDate' => 'required|date',
            'TerminationReason' => 'required|exists:t_CodeDetails,ID',
            'Remarks' => 'nullable|string|max:100',
        ];
    }
}
