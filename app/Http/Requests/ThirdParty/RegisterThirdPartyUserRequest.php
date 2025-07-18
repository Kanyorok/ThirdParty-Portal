<?php

namespace App\Http\Requests\ThirdParty;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
// Removed unused imports: ThirdPartyUser, ValidationException, Str

class RegisterThirdPartyUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Anyone can register
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'UserID' => ['required', 'string', 'max:100', 'min:3', 'unique:t_ThirdPartyUsers,UserID'],
            'FirstName' => ['required', 'string', 'max:255'], // Assuming client sends PascalCase
            'LastName' => ['required', 'string', 'max:255'],  // Assuming client sends PascalCase
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:t_ThirdPartyUsers,Email'],
            'Phone' => ['required', 'string', 'max:20'],      // Assuming client sends PascalCase
            'ThirdPartyID' => ['required', 'integer', 'exists:t_ThirdParties,Id'], // Assuming client sends PascalCase
            'Password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
            'Password_confirmation' => ['required', 'string'], // Ensure this matches the 'confirmed' rule's expectation
        ];
    }
}
