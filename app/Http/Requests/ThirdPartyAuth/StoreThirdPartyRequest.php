<?php

namespace App\Http\Requests\ThirdPartyAuth;

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
            // TODO: Add certications uplod for thirdparties
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('Email') && !filter_var($this->input('Email'), FILTER_VALIDATE_EMAIL)) {
                $validator->errors()->add('Email', __('auth.invalid_email_format'));
            }
            $user = $this->user('sanctum');
            if ($user && $user->thirdParty) {
                $validator->errors()->add('ThirdParty', __('auth.thirdparty_exists'));
            }
        });
    }
}
