<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Foundation\Http\FormRequest;
<<<<<<< HEAD
=======
// use Illuminate\Validation\Rule;
use App\Enums\ThirdParty\ThirdPartyTypeEnum;
>>>>>>> origin/dev

class LoginThirdPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
<<<<<<< HEAD
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string'],
=======
            'profile_type' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::in($allowedLabels),
            ],
            'email' => 'required|email',
            'password' => ['required', 'string', Password::min(8)],
>>>>>>> origin/dev
        ];
    }

    public function messages(): array
    {
        return [
<<<<<<< HEAD
=======
            'profile_type.required' => 'The profile type is required.',
            'profile_type.in' => 'The selected profile type is invalid.',
>>>>>>> origin/dev
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
