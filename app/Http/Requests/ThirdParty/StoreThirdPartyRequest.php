<?php

namespace App\Http\Requests\ThirdParty;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyStatusEnum;

class StoreThirdPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ThirdPartyName' => ['required', 'string', 'max:255', 'unique:t_ThirdParties,ThirdPartyName'],
            'TradingName' => ['nullable', 'string', 'max:255'],
            'BusinessType' => ['nullable', 'string', 'max:100'],
            'RegistrationNumber' => ['nullable', 'string', 'max:100', 'unique:t_ThirdParties,RegistrationNumber'],
            'TaxPIN' => ['nullable', 'string', 'max:50', 'unique:t_ThirdParties,TaxPIN'],
            'VATNumber' => ['nullable', 'string', 'max:50'],
            'Country' => ['nullable', 'string', 'max:100'],
            'PhysicalAddress' => ['nullable', 'string', 'max:500'],
            'Email' => ['required', 'string', 'email', 'max:255', 'unique:t_ThirdParties,Email'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Website' => ['nullable', 'url', 'max:255'],
            'ApprovalStatus' => ['nullable', 'string', Rule::in(['Pending', 'Approved', 'Rejected'])],
            'Status' => ['nullable', Rule::enum(ThirdPartyStatusEnum::class)],
            'ThirdPartyType' => ['required', Rule::enum(ThirdPartyTypeEnum::class)],
        ];
    }
}
