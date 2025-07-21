<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Validation\Rules\Password;

use Illuminate\Foundation\Http\FormRequest;

class LoginThirdPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; //any dude can attempt to log in
    }

    public function rules(): array
    {
        /**
         * Validation rules for login request.
         *
         * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
         */
        return [
            'email' => 'required|email',
            'password' => ['required', 'string', Password::min(8)],
        ];
    }

    /**
     * Custom error messages for validation rules.
     * Return as 422 (unprocessable entity) if validation fails.
     * @return array<string, string>
     */
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
