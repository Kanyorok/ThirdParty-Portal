<?php

namespace App\Http\Requests\ThirdParty;

use Illuminate\Foundation\Http\FormRequest;

class RegisterThirdPartyDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ThirdPartyName'     => 'required|string|max:255',
            'TradingName'        => 'nullable|string|max:255',
            'RegistrationNumber' => 'required|string|max:100',
            'TaxPIN'             => 'required|string|max:100',
            'BusinessType'       => 'required|integer',
            'CountryId'          => 'required|integer',
            'PhysicalAddress'    => 'required|string|max:500',
            'Website'            => 'nullable|url|max:255',
            'accountType'        => 'required|string|in:supplier,tenant,customer',
            'supplierCategories' => 'required_if:accountType,supplier|array',
            'supplierCategories.*' => 'integer|exists:t_SupplierCategories,Id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'LocationId' => $this->LocationId ?? 1,
            'Status'     => $this->Status ?? 1,
        ]);
    }
}
