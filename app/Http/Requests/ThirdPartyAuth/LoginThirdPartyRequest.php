<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
// use Illuminate\Validation\Rule;
use App\Enums\ThirdParty\ThirdPartyTypeEnum;

class LoginThirdPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedLabels = array_map(fn($enum) => $enum->label(), ThirdPartyTypeEnum::cases());

        return [
            'profile_type' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::in($allowedLabels),
            ],
            'email' => 'required|email',
            'password' => ['required', 'string', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_type.required' => 'The profile type is required.',
            'profile_type.in' => 'The selected profile type is invalid.',
            'email.required' => __('auth.email_required'),
            'email.email' => __('auth.invalid_email_format'),
            'password.required' => __('auth.password_required'),
        ];
    }
}
