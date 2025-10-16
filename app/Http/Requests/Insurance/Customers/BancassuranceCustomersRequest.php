<?php

namespace App\Http\Requests\Insurance\Customers;

use Illuminate\Foundation\Http\FormRequest;

class BancassuranceCustomersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ThirdPartyId' => 'required|exists:t_ThirdParties,Id',
            'ReferralID' => 'nullable|exists:t_BancassuranceReferrals,Id',
            'DateOfBirth' => 'required|date|max:100',
            'Gender' => 'required|exists:t_CodeDetails,ID',
            'MaritalStatus' => 'required|exists:t_CodeDetails,ID',
            'Occupation' => 'required|exists:t_CodeDetails,ID',
        ];
    }
}
