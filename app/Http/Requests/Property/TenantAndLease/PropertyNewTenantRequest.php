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
            'TenantType' => 'required|exists:t_CodeDetails,ID',
            'TenantName' => 'required|string|max:100',
            'IDRegistrationNo' => 'required|string|max:50',
            'PhoneNumber' => 'required|string|max:50',
            'EmailAddress' => 'required|string|max:100',
            'Nationality' => 'required|string|max:50',
            'PostalAddress' => 'required|string|max:50',
            'Remarks' => 'required|string|max:255',
            'IsActive' => 'boolean',
            'Document' => 'nullable|file|max:2048',
        ];
    }
}
