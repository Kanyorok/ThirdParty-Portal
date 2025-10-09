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
            'ReferralID' => 'nullable|exists:t_BancassuranceReferrals,Id',
            'FullName' => 'required|string|max:100',
            'NationalID' => 'required|string|max:100',
            'KRAPIN' => 'required|string|max:100',
            'DateOfBirth' => 'required|date|max:100',
            'Gender' => 'required|exists:t_CodeDetails,ID',
            'MaritalStatus' => 'required|exists:t_CodeDetails,ID',
            'PhoneNumber' => 'required|string|max:100',
            'Email' => 'required|string|max:100',
            'Address' => 'required|string|max:100',
            'Occupation' => 'required|exists:t_CodeDetails,ID',
        ];
    }
}
