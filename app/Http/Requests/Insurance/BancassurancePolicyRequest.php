<?php

namespace App\Http\Requests\Insurance;

use App\Enums\Insurance\InsurancePolicyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BancassurancePolicyRequest extends FormRequest
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
            'CustomerID' => 'required|exists:t_BancassuranceCustomers,Id',
            'ProductID' => 'required|exists:t_CodeDetails,ID',
            'InsurerID' => 'nullable|exists:t_CodeDetails,ID',
            'SumAssured' => 'required|numeric|min:0',
            'PremiumAmount' => 'required|numeric|min:0',
            'PolicyStartDate' => 'required|date',
            'PolicyEndDate' => 'required|date|after_or_equal:PolicyStartDate',
            'PaymentFrequency' => 'required|exists:t_CodeDetails,ID',
            'ReferralID' => 'nullable|exists:t_BancassuranceReferrals,Id',
            'IssuedDate' => 'nullable|date',
            'ExpiryDate' => 'nullable|date|after_or_equal:IssuedDate',
            'IsActive' => 'boolean',
            'Status'    => ['required', new Enum(InsurancePolicyStatus::class)],
            'Document' => 'nullable|file|max:2048'
        ];
    }
}
