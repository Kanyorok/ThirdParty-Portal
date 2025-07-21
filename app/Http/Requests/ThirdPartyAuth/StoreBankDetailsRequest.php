<?php

namespace App\Http\Requests\ThirdPartyBankDetail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->guard('sanctum')->user();

        if (!$user) {
            return false;
        }

        // if ($user->isAdmin()) {
        //     return true;
        // }

        $thirdPartyId = $this->input('ThirdPartyId');

        return $user->thirdParty && $user->thirdParty->Id === (int) $thirdPartyId;
    }

    public function rules(): array
    {
        return [
            'ThirdPartyId' => 'required|integer|exists:t_ThirdParties,Id',
            'BankName' => 'required|string|max:255',
            'Branch' => 'nullable|string|max:255',
            'AccountNumber' => [
                'required',
                'string',
                'max:100',
                Rule::unique('t_ThirdPartiesBankDetails', 'AccountNumber')->where(function ($query) {
                    return $query->where('ThirdPartyId', $this->input('ThirdPartyId'));
                }),
            ],
            'CurrencyId' => 'required|integer|exists:t_Currencies,Id',
            'SwiftCode' => 'nullable|string|max:50',
        ];
    }
}
