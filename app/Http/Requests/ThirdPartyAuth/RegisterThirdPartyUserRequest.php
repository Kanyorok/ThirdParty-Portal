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
            'Password.confirmed' => __('auth.password_mismatch'),
        ];
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
