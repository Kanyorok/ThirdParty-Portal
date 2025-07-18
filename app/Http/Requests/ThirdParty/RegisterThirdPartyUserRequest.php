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
            'FirstName' => ['required', 'string', 'max:255'],
            'LastName' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:t_ThirdPartyUsers,Email'],
            'Phone' => ['required', 'string', 'max:20'],
            'ThirdPartyId' => ['required', 'integer', 'exists:t_ThirdParties,Id'],
            'Password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
            'Password_confirmation' => ['required', 'string'],
        ];
    }
}
