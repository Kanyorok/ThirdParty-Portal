<?php

namespace App\Http\Requests\Property\BillingAndReceipting;

use Illuminate\Foundation\Http\FormRequest;

class PropertyReceiptRequest extends FormRequest
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
            'InvoiceID' => 'required',
            'BillingMonth' => 'required',
            'InvoiceDate' => 'required',
            'RentAmount' => 'required',
            'ServicesCharge' => 'required',
            'OtherCharges' => 'required',
            'TotalDue' => 'required',
            'AmountPaid' => 'required',
            'Balance' => 'required',
            'PaymentDate' => 'required',
            'Amount' => 'required',
            'PaymentMethod' => 'required',
            'ReferenceNo' => 'required',
            'Remarks' => 'required',
        ];
    }
}
