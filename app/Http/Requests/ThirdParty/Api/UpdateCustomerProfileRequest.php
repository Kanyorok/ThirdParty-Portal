<?php

namespace App\Http\Requests\ThirdParty\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'DateOfBirth' => ['sometimes', 'date', 'before:today'],
            'Gender' => ['sometimes', 'integer', 'exists:t_CodeDetails,ID'],
            'MaritalStatus' => ['sometimes', 'integer', 'exists:t_CodeDetails,ID'],
            'Occupation' => ['sometimes', 'integer', 'exists:t_CodeDetails,ID'],
        ];
    }

    public function messages(): array
    {
        return [
            'DateOfBirth.before' => 'Date of birth must be in the past',
            'Gender.exists' => 'Invalid gender selected',
            'MaritalStatus.exists' => 'Invalid marital status selected',
            'Occupation.exists' => 'Invalid occupation selected',
        ];
    }
}
