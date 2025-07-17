<?php

namespace App\Http\Requests\ThirdParty;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class RegisterThirdPartyUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        Log::info('Request data:', $this->all());
        return [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:t_ThirdPartyUsers,Email'],
            'phone' => ['required', 'string', 'max:20'],
            'thirdPartyId' => ['required', 'integer', 'exists:t_ThirdParties,Id'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ];
    }
}
