<?php

namespace App\Http\Requests\Property\BillingAndReceipting;

use Illuminate\Foundation\Http\FormRequest;

class PropertyInvoiceRequest extends FormRequest
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
            'Lease' => ['required', 'exists:t_LeaseCreation,Id'],
            'BillingMonth' => ['required', 'date'],
            'InvoiceDate' => ['required', 'date'],

            // Line amounts
            'RentAmount' => ['required', 'numeric', 'min:0'],
            'ServicesCharge' => ['nullable', 'numeric', 'min:0'],
            'ParkingFee' => ['nullable', 'numeric', 'min:0'],
            'OtherCharges' => ['nullable', 'numeric', 'min:0'],

            // Descriptions
            'Description' => ['nullable', 'string', 'max:255'],
            'DescriptionService' => ['nullable', 'string', 'max:255'],
            'DescriptionParking' => ['nullable', 'string', 'max:255'],
            'DescriptionOther' => ['nullable', 'string', 'max:255'],
            'DescriptionRent' => ['nullable', 'string', 'max:255'],


            // Currency fields
            'CurrencyRent' => ['required', 'exists:t_Currencies,Id'],
            'CurrencyService' => ['nullable', 'exists:t_Currencies,Id'],
            'CurrencyParking' => ['nullable', 'exists:t_Currencies,Id'],
            'CurrencyOther' => ['nullable', 'exists:t_Currencies,Id'],

            // Tax fields
            'TaxRent' => ['required', 'exists:t_FinanceTaxType,Id'],
            'TaxService' => ['nullable', 'exists:t_FinanceTaxType,Id'],
            'TaxParking' => ['nullable', 'exists:t_FinanceTaxType,Id'],
            'TaxOther' => ['nullable', 'exists:t_FinanceTaxType,Id'],

            'InvoiceNotes' => ['nullable', 'string', 'max:500'],
        ];
    }

}
