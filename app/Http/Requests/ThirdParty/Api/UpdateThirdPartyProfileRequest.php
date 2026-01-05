<?php

namespace App\Http\Requests\ThirdParty\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThirdPartyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $thirdPartyId = $user?->thirdParty?->Id;

        return [
            'ThirdPartyName' => ['sometimes', 'string', 'max:255'],
            'TradingName' => ['sometimes', 'string', 'max:255'],
            'BusinessType' => ['sometimes', 'integer', 'exists:BusinessTypes,BusinessTypeId'],
            'RegistrationNumber' => ['sometimes', 'string', 'max:50'],
            'TaxPIN' => ['sometimes', 'string', 'max:50'],
            'CountryId' => ['sometimes', 'integer', 'exists:countries,CountryId'],
            'LocationId' => ['sometimes', 'integer', 'exists:Localities,LocalityId'],
            'Email' => ['sometimes', 'email', 'max:255', 'unique:t_ThirdParties,Email,' . $thirdPartyId . ',Id'],
            'Phone' => ['sometimes', 'string', 'max:20'],
            'PostalAddress' => ['sometimes', 'string', 'max:255'],
            'PhysicalAddress' => ['sometimes', 'string', 'max:255'],
            'Website' => ['sometimes', 'string', 'max:255', 'url'],
        ];
    }
}
