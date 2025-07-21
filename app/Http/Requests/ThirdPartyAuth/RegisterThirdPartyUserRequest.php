<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterThirdPartyUserRequest extends FormRequest
{
    /**
     * user is authorized to make this request.?
     */
    public function authorize(): bool
    {
        return true; // Anyone can register
    }

    /**
     * validation rules for the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'UserID' => ['required', 'string', 'max:100', 'min:3', 'unique:t_ThirdPartyUsers,UserID'],
            'FirstName' => ['required', 'string', 'max:255'],
            'LastName' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:t_ThirdPartyUsers,Email'],
            'Phone' => ['required', 'string', 'max:20'],
            // 'ThirdPartyId' => ['required', 'integer', 'exists:t_ThirdParties,Id'],
            'Password' => ['required', 'string', 'min:8', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()->symbols()],
            'Password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Messages; custom ; error.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'Email.unique' => __('auth.user_exists'),
            'Password.confirmed' => __('auth.password_mismatch'),
        ];
    }
}
