<?php

namespace App\Http\Requests\ThirdParty;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class RegisterThirdPartyUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'FirstName' => ['required', 'string', 'min:2', 'max:100'],
            'LastName' => ['required', 'string', 'min:2', 'max:100'],
            'Email' => [
                'required',
                'string',
                'email:rfc,filter',
                'max:255',
                'unique:t_ThirdPartyUsers,Email',
            ],
            'Phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+[1-9]\d{7,14}$/',
                'unique:t_ThirdPartyUsers,Phone',
            ],
            'Password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'Password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'FirstName.required' => __('validation.first_name_required'),
            'FirstName.min' => __('validation.first_name_min'),
            'LastName.required' => __('validation.last_name_required'),
            'LastName.min' => __('validation.last_name_min'),
            'Email.required' => __('auth.email_required'),
            'Email.email' => __('auth.invalid_email_format'),
            'Email.unique' => __('auth.user_exists'),
            'Phone.required' => __('validation.phone_required'),
            'Phone.regex' => __('auth.invalid_phone_format'),
            'Phone.unique' => __('validation.phone_exists'),
            'Password.required' => __('auth.password_required'),
            'Password.confirmed' => __('auth.password_mismatch'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'Email' => strtolower(trim($this->Email ?? '')),
            'FirstName' => ucfirst(strtolower(trim($this->FirstName ?? ''))),
            'LastName' => ucfirst(strtolower(trim($this->LastName ?? ''))),
        ]);

        $phone = preg_replace('/\D+/', '', $this->Phone ?? '');
        if (strlen($phone) >= 8 && strlen($phone) <= 15) {
            $this->merge(['Phone' => '+' . ltrim($phone, '0')]);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('single')->warning('Third-party registration validation failed', [
            'errors' => $validator->errors()->toArray(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);

        throw new HttpResponseException(response()->json([
            'message' => __('auth.validation_failed'),
            'errors' => $validator->errors(),
        ], 422));
    }

    public function validatedData(): array
    {
        $data = parent::validated();
        unset($data['Password_confirmation']);
        return $data;
    }
}
