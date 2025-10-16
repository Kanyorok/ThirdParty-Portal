<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyNewTenantRequest extends FormRequest
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
            'ThirdPartyId' => 'required|exists:t_ThirdParties,Id',
            'TenantType' => 'required|exists:t_CodeDetails,ID',
            'Remarks' => 'nullable|string|max:255',
            'IsActive' => 'boolean',
            'Document' => 'nullable|file|max:9048',
        ];
    }
}
