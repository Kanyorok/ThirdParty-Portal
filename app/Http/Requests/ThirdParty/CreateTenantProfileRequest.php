<?php

namespace App\Http\Requests\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Rules\ThirdParty\UniqueProfileType;
use App\Rules\ThirdParty\ValidRegistrationNumber;
use App\Rules\ThirdParty\ValidTaxPin;
use Illuminate\Foundation\Http\FormRequest;

class CreateTenantProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            && $this->user()->isActive()
            && $this->user()->hasVerifiedEmail();
    }

    public function rules(): array
    {
        return [
            'third_party_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                new UniqueProfileType($this->user(), ThirdPartyTypeEnum::Tenant),
            ],
            'trading_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'exists:t_CodeDetails,Id'],
            'registration_number' => ['nullable', 'string', 'max:100', new ValidRegistrationNumber],
            'tax_pin' => ['nullable', 'string', 'max:50', new ValidTaxPin],
            'country_id' => ['nullable', 'exists:t_Countries,Id'],
            'location_id' => ['nullable', 'exists:t_Localities,ID'],
            'physical_address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255', 'unique:t_ThirdParties,Email'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/'],
            'website' => ['nullable', 'url', 'max:255'],
            'tenant_type' => ['nullable', 'exists:t_CodeDetails,Id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'third_party_name.required' => 'Name is required',
        ];
    }
}
