<?php

namespace App\Http\Requests\ThirdParty;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\BusinessTypeEnum;
use App\Enums\ThirdPartyTypeEnum;

class RegisterThirdPartyDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'exists:t_ThirdPartyUsers,UserID'],
            'ThirdPartyName' => ['required', 'string', 'max:255'],
            'TradingName' => ['nullable', 'string', 'max:255'],
            'BusinessType' => ['required', 'string', Rule::in(array_column(BusinessTypeEnum::cases(), 'value'))],
            'RegistrationNumber' => ['required', 'string', 'max:255', 'unique:t_ThirdParties,RegistrationNumber'],
            'TaxPIN' => ['nullable', 'string', 'max:255'],
            'VATNumber' => ['nullable', 'string', 'max:255'],
            'Country' => ['required', 'string', 'max:255'],
            'PhysicalAddress' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:t_ThirdParties,Email'],
            'Phone' => ['required', 'string', 'max:20'],
            'Website' => ['nullable', 'string', 'url', 'max:255'],
            'ThirdPartyType' => ['required', 'string', Rule::in(array_column(ThirdPartyTypeEnum::cases(), 'value'))],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => __('auth.user_id_not_found'),
            'RegistrationNumber.unique' => __('thirdparty.registration_number_exists'),
            'Email.unique' => __('thirdparty.email_exists'),
        ];
    }
}
