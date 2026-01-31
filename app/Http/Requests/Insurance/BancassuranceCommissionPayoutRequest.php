<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class BancassuranceCommissionPayoutRequest extends FormRequest
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
            'PolicyId' => 'required|exists:t_BancassurancePolicies,Id',
            'PayoutReference' => 'nullable|string|max:255',
            'PaidAmount' => 'required|numeric',
            'CurrencyId' => 'required|exists:t_Currencies,Id',
            'CommissionRuleId' => 'nullable|exists:t_BancassuranceCommissionRules,Id',
            'PaymentDate' => 'required|date',
            'PaymentMode' => 'required|exists:t_CodeDetails,ID',
            'Remarks' => 'nullable|string|max:255',
            'PaidTo' => 'required|exists:t_Users,Id',
        ];
    }
}
