<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ThirdPartyStatusEnum;
use App\Enums\BusinessTypeEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;

class UpdateThirdPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('sanctum')->check();
    }

    public function rules(): array
    {
        $partyId = $this->route('party') ? $this->route('party')->Id : null;

        return [
            'ThirdPartyName' => ['required', 'string', 'max:255'],
            'TradingName' => ['nullable', 'string', 'max:255'],
            'BusinessType' => ['required', 'string', Rule::in(array_column(BusinessTypeEnum::cases(), 'value'))],
            'RegistrationNumber' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('t_ThirdParties', 'RegistrationNumber')->ignore($partyId, 'Id'),
            ],
            'TaxPIN' => ['nullable', 'string', 'max:255'],
            'VATNumber' => ['nullable', 'string', 'max:255'],
            'Country' => ['required', 'string', 'max:255'],
            'PhysicalAddress' => ['required', 'string', 'max:500'],
            'Email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('t_ThirdParties', 'Email')->ignore($partyId, 'Id'),
            ],
            'Phone' => ['required', 'string', 'max:20'],
            'Website' => ['nullable', 'string', 'url', 'max:255'],
            'ApprovalStatus' => ['required', 'string', Rule::in(array_column(ThirdPartyApprovalStatusEnum::cases(), 'value'))],
            'Status' => ['required', 'string', Rule::in(array_column(ThirdPartyStatusEnum::cases(), 'value'))],
            'IsPrequalified' => ['sometimes', 'boolean'],
        ];
    }
}
