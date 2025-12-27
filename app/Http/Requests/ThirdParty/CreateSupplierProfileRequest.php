<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreSupplierRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'FirstName'          => ['required', 'string', 'max:50'],
            'LastName'           => ['required', 'string', 'max:50'],
            'Email'              => ['required', 'email', 'unique:t_ThirdPartyUsers,Email'],
            'Phone'              => ['required', 'string', 'max:20'],
            'Password'           => ['required', 'confirmed', Password::defaults()],

            'ThirdPartyName'     => ['required', 'string', 'max:255'],
            'TradingName'        => ['nullable', 'string', 'max:255'],
            'RegistrationNumber' => ['required', 'string', 'unique:t_ThirdParties,RegistrationNumber'],
            'TaxPIN'             => ['required', 'string', 'unique:t_ThirdParties,TaxPIN'],
            'BusinessType'       => ['required', 'integer', 'exists:t_CodeDetails,Id'],
            'CountryId'          => ['required', 'integer', 'exists:t_Countries,Id'],

            'ThirdPartyType'     => ['required', 'integer', 'exists:t_ThirdPartyTypes,Id'],
        ];
    }
}
