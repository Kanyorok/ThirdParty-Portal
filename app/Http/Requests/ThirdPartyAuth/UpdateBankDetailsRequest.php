<?php

namespace App\Http\Requests\ThirdPartyAuth;

use App\Models\ThirdParty\ThirdPartiesBankDetails;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $bankDetail = $this->bankDetail();

        return $user
            && $bankDetail
            && $user->thirdParty
            && $user->thirdParty->Id === $bankDetail->ThirdPartyId;
    }

    public function rules(): array
    {
        $bankDetailId = $this->route('third_parties_bank_detail')->BankID;

        return [
            'ThirdPartyId' => 'sometimes|required|integer|exists:t_ThirdParties,Id',
            'BankName' => 'sometimes|required|string|max:255',
            'Branch' => 'nullable|string|max:255',
            'AccountNumber' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^\d+$/',
                Rule::unique('t_ThirdPartiesBankDetails', 'AccountNumber')->ignore($bankDetailId, 'BankID')->where(function ($query) {
                    return $query->where('ThirdPartyId', $this->input('ThirdPartyId', $this->route('third_parties_bank_detail')->ThirdPartyId));
                }),
            ],
            'CurrencyId' => 'sometimes|required|integer|exists:t_Currencies,Id',
            'SwiftCode' => 'nullable|string|max:50',
        ];
    }

    public function bankDetail(): ?ThirdPartiesBankDetails
    {
        return $this->route('third_parties_bank_detail');
    }
}
