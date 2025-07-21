<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyStatusEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;

class UpdateThirdPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('sanctum')->check();
    }

    public function rules(): array
    {
        $thirdPartyId = $this->route('third_party')->Id;

        return [
            'thirdPartyName' => ['sometimes', 'string', 'max:255', Rule::unique('t_ThirdParties', 'ThirdPartyName')->ignore($thirdPartyId, 'Id')],
            'tradingName' => ['nullable', 'string', 'max:255'],
            'businessType' => ['nullable', 'string', 'max:100'],
            'registrationNumber' => ['nullable', 'string', 'max:100', Rule::unique('t_ThirdParties', 'RegistrationNumber')->ignore($thirdPartyId, 'Id')],
            'taxPin' => ['nullable', 'string', 'max:50', Rule::unique('t_ThirdParties', 'TaxPIN')->ignore($thirdPartyId, 'Id')],
            'vatNumber' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'physicalAddress' => ['nullable', 'string', 'max:500'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('t_ThirdParties', 'Email')->ignore($thirdPartyId, 'Id')],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'approvalStatus' => ['nullable', Rule::enum(ThirdPartyApprovalStatusEnum::class)], // Use enum for validation
            'status' => ['nullable', Rule::enum(ThirdPartyStatusEnum::class)],
            'thirdPartyType' => ['sometimes', Rule::enum(ThirdPartyTypeEnum::class)],
        ];
    }
}
