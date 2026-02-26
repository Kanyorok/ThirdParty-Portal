<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return (bool) ($user && $user->thirdParty);
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        $payload = [];

        if ($user?->thirdParty?->Id) {
            // Always bind writes to the authenticated third party.
            $payload['ThirdPartyId'] = (int) $user->thirdParty->Id;
        }

        if ($this->has('BranchId') && ! $this->has('BranchID')) {
            $payload['BranchID'] = $this->input('BranchId');
        }

        if (! empty($payload)) {
            $this->merge($payload);
        }
    }

    public function rules(): array
    {
        return [
            'ThirdPartyId' => 'required|integer|exists:t_ThirdParties,Id',
            'BankName' => 'nullable|string|max:255',
            'Branch' => 'nullable|string|max:255',
            'BranchID' => 'nullable|integer|exists:t_BankBranches,BranchID',
            'AccountNumber' => [
                'required',
                'string',
                'max:100',
                'regex:/^\d+$/',
                Rule::unique('t_ThirdPartiesBankDetails', 'AccountNumber')->where(function ($query) {
                    return $query->where('ThirdPartyId', $this->input('ThirdPartyId'));
                }),
            ],
            'CurrencyId' => 'required|integer|exists:t_Currencies,Id',
            'SwiftCode' => 'nullable|string|max:50',
        ];
    }
}
