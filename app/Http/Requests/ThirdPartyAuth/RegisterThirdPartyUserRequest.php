<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Enums\BusinessTypeEnum;

class RegisterThirdPartyUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'FirstName' => ['required', 'string', 'max:255'],
            'LastName' => ['required', 'string', 'max:255'],
            'Email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:t_ThirdPartyUsers,Email',
                'unique:t_ThirdParties,Email'
            ],
            'Phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+[1-9]\d{7,14}$/',
                'unique:t_ThirdPartyUsers,Phone',
                'unique:t_ThirdParties,Phone'
            ],
            'Password' => ['required', 'string', 'min:8', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'Password_confirmation' => ['required', 'string'],
            'ThirdPartyName' => ['nullable', 'string', 'max:255'],
            'TradingName' => ['nullable', 'string', 'max:255'],
            'BusinessType' => ['nullable', 'string', 'max:255', Rule::enum(BusinessTypeEnum::class)],
            'RegistrationNumber' => ['nullable', 'string', 'max:255'],
            'TaxPIN' => ['nullable', 'string', 'max:255'],
            'VATNumber' => ['nullable', 'string', 'max:255'],
            'CountryId' => ['nullable', 'integer', 'exists:t_Country,Id'],
            'PhysicalAddress' => ['nullable', 'string', 'max:500'],
            'Website' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'Email.unique' => __('auth.user_exists'),
            'Phone.regex' => __('auth.invalid_phone_format'),
            'Password.confirmed' => __('auth.password_mismatch'),
            'ThirdPartyType.required' => 'A Third Party Type (T, S, or C) must be selected for registration.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $raw = (string) ($this->input('Phone') ?? '');
        if ($raw === '') {
            return;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if (strlen($digits) >= 8 && strlen($digits) <= 15) {
            $normalized = '+' . ltrim($digits, '+');
            $this->merge(['Phone' => $normalized]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        Log::channel('single')->warning('Third-party registration validation failed', [
            'errors' => $validator->errors()->toArray(),
            'ip' => $this->ip(),
            'forwarded_for' => $this->header('X-Forwarded-For'),
            'user_agent' => $this->userAgent(),
            'url' => $this->fullUrl(),
            'route' => optional($this->route())->getName(),
            'payload' => $this->except(['Password', 'Password_confirmation']),
        ]);

        throw new HttpResponseException(response()->json([
            'message' => __('auth.validation_failed'),
            'errors' => $validator->errors(),
        ], 422));
    }
}
