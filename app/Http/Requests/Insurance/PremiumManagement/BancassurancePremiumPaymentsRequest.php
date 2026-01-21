<?php

namespace App\Http\Requests\Insurance\PremiumManagement;

use Illuminate\Foundation\Http\FormRequest;

class BancassurancePremiumPaymentsRequest extends FormRequest
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
            'PolicyID' => 'required|exists:t_BancassurancePolicies,Id',
            'CustomerID' => 'required|string|max:100',
            'PaymentFrequency' => 'required|string|max:50',
            'PaymentDate' => 'required|date',
            'NextPaymentDate' => 'required|date',
            'Amount' => 'required|numeric|min:1',
            'CurrencyId' => 'required|exists:t_Currencies,Id',
            'PaymentMode' => 'required|exists:t_CodeDetails,ID',
            'ReferenceNumber' => 'nullable|string|max:100',
            'Notes' => 'nullable|string|max:255',
        ];
    }
}
