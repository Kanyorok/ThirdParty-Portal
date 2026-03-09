<?php

namespace App\Http\Requests\ThirdParty;

use Illuminate\Foundation\Http\FormRequest;

class RegisterThirdPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'FirstName' => ['required', 'string', 'max:100'],
            'LastName' => ['required', 'string', 'max:100'],
            'Email' => ['required', 'email', 'unique:t_ThirdPartyUsers,Email'],
            'Phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/'],
            'Password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'Email.unique' => 'This email address is already registered.',
            'Phone.regex' => 'Phone number must contain digits only and may start with +.',
            'Password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
