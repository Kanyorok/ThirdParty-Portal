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
                'Lease' => 'required|exists:t_LeaseCreation,Id',
                'BillingMonth' => 'required|string|max:50',
                'InvoiceDate' => 'required|date',
                'RentAmount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
                'ServicesCharge' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                'OtherCharges' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                'ParkingFee' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                'InvoiceNotes' => 'nullable|string|max:100',
                'Description' => 'nullable|string|max:255',
                'Currency' => 'nullable|exists:t_Currencies,Id',
                'Tax' => 'nullable|exists:t_FinanceTaxType,Id',
            ];
    }
}
