<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => __('auth.reset_token_required'),
            'email.required' => __('auth.email_required'),
            'email.email' => __('auth.invalid_email_format'),
            'password.required' => __('validation.new_password_required'),
            'password.confirmed' => __('validation.password_confirmation_mismatch'),
        ];
    }
}
