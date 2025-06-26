<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyTenantClearanceRequest extends FormRequest
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
            'Tenant' => 'required|exists:t_TenantMaintenance,Id',
            'ExitDate' => 'required|date',
            'FinalInspection' => 'required|boolean',
            'AllDuesPaid' => 'required|boolean',
            'KeysReturned' => 'required|boolean',
            'DepositRefunded' => 'required|exists:t_CodeDetails,Id',
            'AdditionalNotes' => 'nullable|string',
        ];
    }
}
