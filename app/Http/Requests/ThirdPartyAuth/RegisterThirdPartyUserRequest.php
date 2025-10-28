<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

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
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:t_ThirdPartyUsers,Email'],
            'Phone' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'Password' => ['required', 'string', 'min:8', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'Password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'Email.unique' => __('auth.user_exists'),
            'Phone.regex' => 'Phone format is invalid. Use international format, e.g., +254712345678',
            'Password.confirmed' => __('auth.password_mismatch'),
        ];
    }

    /**
     * Normalize phone to +E.164 before validation (allow inputs like 2547..., +2547..., spaces, dashes).
     */
    protected function prepareForValidation(): void
    {
        $raw = (string) ($this->input('Phone') ?? '');
        if ($raw === '') {
            return;
        }

        // Strip everything except digits
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        // If we have a plausible E.164 length, prefix with + (8-15 digits total)
        if (strlen($digits) >= 8 && strlen($digits) <= 15) {
            $normalized = '+' . ltrim($digits, '+');
            $this->merge(['Phone' => $normalized]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        // Log validation errors with request context to single channel
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
            'message' => __('validation.failed'),
            'errors' => $validator->errors(),
        ], 422));
    }
}
