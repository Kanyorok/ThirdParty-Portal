<?php

namespace App\Http\Requests\ThirdPartyAuth;

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
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string'],
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
        ]);
    }
}
