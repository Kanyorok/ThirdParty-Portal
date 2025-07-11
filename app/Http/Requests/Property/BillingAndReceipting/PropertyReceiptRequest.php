<?php

namespace App\Http\Requests\Property\BillingAndReceipting;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        return [
            'InvoiceID' => 'required',
            'BillingMonth' => 'required|date',
            'InvoiceDate' => 'required',
            'RentAmount' => 'required',
            'ServicesCharge'=>'required',
            'OtherCharges'=>'required',
            'TotalDue' => 'required|numeric',
            'AmountPaid' => 'required|numeric',
            'Balance' => 'required|numeric',
            'PaymentDate' => 'required|date',
            'Amount' => 'required|numeric',
            'PaymentMethod' => 'required|string|max:100',
            'ReferenceNo' => 'required|string|max:100',
            'Remarks' => 'required|string|max:100',
        ];
    }
}
