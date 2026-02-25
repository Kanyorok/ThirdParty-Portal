<?php

namespace App\Http\Requests\ThirdPartyAuth;

use App\Enums\Employee\GenderEnum;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateThirdPartyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->guard('sanctum')->user();

        return $user instanceof ThirdPartyUser && ! is_null($user->thirdParty);
    }

    public function rules(): array
    {
        $thirdPartyId = auth()->guard('sanctum')->user()?->thirdParty?->Id;

        return [
            'firstName' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z\s\'-]+$/'],
            'lastName' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z\s\'-]+$/'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'gender' => ['nullable', Rule::enum(GenderEnum::class)],
            'imageId' => ['nullable', 'integer', 'exists:t_Images,Id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:t_SupplierCategories,Id'],

            'tradingName' => ['nullable', 'string', 'max:255'],
            'businessType' => ['nullable', 'string', 'max:100'],
            'registrationNumber' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('t_ThirdParties', 'RegistrationNumber')->ignore($thirdPartyId, 'Id'),
            ],
            'taxPin' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('t_ThirdParties', 'TaxPIN')->ignore($thirdPartyId, 'Id'),
            ],
            'vatNumber' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'countryId' => ['nullable', 'integer', 'exists:t_Countries,Id'],
            'physicalAddress' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
