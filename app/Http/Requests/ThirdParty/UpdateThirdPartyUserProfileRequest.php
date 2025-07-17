<?php

namespace App\Http\Requests\ThirdParty;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\Employee\GenderEnum;

class UpdateThirdPartyUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('sanctum')->check() && auth()->guard('sanctum')->user() instanceof \App\Models\ThirdPartyUser;
    }

    public function rules(): array
    {
        auth()->guard('sanctum')->id();
        $thirdPartyId = auth()->guard('sanctum')->user()->thirdParty->Id ?? null;

        return [
            'firstName' => ['nullable', 'string', 'max:100'],
            'lastName' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', Rule::enum(GenderEnum::class)],
            'imageId' => ['nullable', 'integer', 'exists:t_Images,Id'],

            'tradingName' => ['nullable', 'string', 'max:255'],
            'businessType' => ['nullable', 'string', 'max:100'],
            'registrationNumber' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('t_ThirdParties', 'RegistrationNumber')->ignore($thirdPartyId, 'Id')
            ],
            'taxPin' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('t_ThirdParties', 'TaxPIN')->ignore($thirdPartyId, 'Id')
            ],
            'vatNumber' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'physicalAddress' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
