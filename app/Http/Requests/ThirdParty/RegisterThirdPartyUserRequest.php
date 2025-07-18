<?php

namespace App\Http\Requests\ThirdParty;

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
            'UserID' => ['required', 'string', 'max:100', 'min:3', 'unique:t_ThirdPartyUsers,UserID'],
            'FirstName' => ['required', 'string', 'max:255'],
            'LastName' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:t_ThirdPartyUsers,Email'],
            'Phone' => ['required', 'string', 'max:20'],
            'ThirdPartyId' => ['required', 'integer', 'exists:t_ThirdParties,Id'],
            'Password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
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
            'UserID.unique' => __('auth.user_exists'),
            'UserID.required' => 'A User ID is required.',
            'UserID.min' => 'The User ID must be at least :min characters.',
            'Email.unique' => __('auth.user_exists'),
            'Password.confirmed' => __('auth.invalid_credentials'),
            'Password.min' => 'The password must be at least :min characters.',
            'FirstName.required' => 'Your first name is required.',
            'Email.email' => 'Please provide a valid email address.',
            'LastName.required' => 'Your last name is required.',
            'Phone.required' => 'Your phone number is required.',
            'ThirdPartyId.exists' => __('auth.3rd_party_not_found'),
            'ThirdPartyId.required' => 'A valid third party ID is required.',
            'Password.required' => 'A password is required.',
            'Password_confirmation.required' => 'Please confirm your password.',
        ];
    }
}
