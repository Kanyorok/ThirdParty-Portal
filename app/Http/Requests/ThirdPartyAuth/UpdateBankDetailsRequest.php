<?php

namespace App\Http\Requests\ThirdPartyBankDetail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $bankDetail = $this->route('third_parties_bank_detail');
        return $user?->hasRole(['Admin']) || ($user && $bankDetail && $user->id === $bankDetail->UserId);
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
                Rule::unique('t_ThirdPartiesBankDetails', 'AccountNumber')->ignore($bankDetailId, 'BankID')->where(function ($query) {
                    return $query->where('ThirdPartyId', $this->input('ThirdPartyId', $this->route('third_parties_bank_detail')->ThirdPartyId));
                }),
            ],
            'CurrencyId' => 'sometimes|required|integer|exists:t_Currencies,Id',
            'SwiftCode' => 'nullable|string|max:50',
        ];
    }
}
