<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\BusinessTypeEnum;

// Legacy enum no longer used for validation of ThirdPartyType; now using dynamic TypeId from t_ThirdPartyTypes

class StoreThirdPartyRequest extends FormRequest
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
            'BusinessType' => ['required', Rule::enum(BusinessTypeEnum::class)],
            'RegistrationNumber' => ['required', 'string', 'max:255', 'unique:t_ThirdParties,RegistrationNumber'],
            'TaxPIN' => ['nullable', 'string', 'max:255', 'unique:t_ThirdParties,TaxPIN'],
            'VATNumber' => ['nullable', 'string', 'max:255'],
            'Country' => ['required', 'string', 'max:255'],
            'PhysicalAddress' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:t_ThirdParties,Email'],
            'Phone' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'Website' => ['nullable', 'url', 'max:255'],
            // Accept new numeric TypeId referencing t_ThirdPartyTypes.TypeId
            'ThirdPartyType' => ['required', 'integer', 'exists:t_ThirdPartyTypes,TypeId'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => __('auth.user_not_found'),
            'RegistrationNumber.unique' => __('auth.registration_number_exists'),
            'TaxPIN.unique' => __('auth.tax_pin_exists'),
            'Email.unique' => __('auth.email_exists'),
        ];
    }
}
