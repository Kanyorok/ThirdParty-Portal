<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class LoginThirdPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => ['required', 'string', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('auth.invalid_credentials'),
            'email.email' => __('auth.invalid_credentials'),
            'password.required' => __('auth.invalid_credentials'),
            'password.min' => __('auth.invalid_credentials'),
        ];
    }
}
