<?php

namespace App\Http\Requests\ThirdParty\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'TenantType' => ['sometimes', 'integer', 'exists:t_CodeDetails,ID'],
            'Remarks' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'TenantType.exists' => 'Invalid tenant type selected',
        ];
    }
}
