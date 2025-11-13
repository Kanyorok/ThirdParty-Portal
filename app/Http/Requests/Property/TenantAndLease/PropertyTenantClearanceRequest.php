<?php

namespace App\Http\Requests\Property\TenantAndLease;

use App\Enums\Property\TenantClearanceEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'LeaseId' => 'required|exists:t_LeaseCreation,Id',
            'ExitDate' => ['required', 'date_format:d/m/Y'],
            'FinalInspection' => 'required|boolean',
            'AllDuesPaid' => 'required|boolean',
            'KeysReturned' => 'required|boolean',
            'DepositRefunded' => 'required|exists:t_CodeDetails,Id',
            'AdditionalNotes' => 'nullable|string',
            'Status' => ['required', new Enum(TenantClearanceEnum::class)],
            'Document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:25000',
        ];
    }
}
