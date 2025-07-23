<?php

namespace App\Http\Requests\Property\BillingAndReceipting;

use App\Enums\Property\PropertyInvoiceEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'InvoiceID' => 'required|exists:t_RentInvoice,Id',
            'BillingMonth' => 'required|date',
            'InvoiceDate' => 'required|date',
            'RentAmount' => 'required|numeric',
            'ServicesCharge'=>'nullable|numeric',
            'ParkingFee' => 'nullable|numeric',
            'OtherCharges'=>'nullable|numeric',
            'TotalDue' => 'required|numeric',
            'AmountPaidSoFar' => 'required|numeric',
            'Balance' => 'required|numeric',
            'PaymentDate' => 'required|date',
            'AmountPaidNow' => 'required|numeric',
            'PaymentMethod' => 'required|exists:t_CodeDetails,ID',
            'ReferenceNo' => 'nullable|string|max:100',
            'Remarks' => 'nullable|string|max:100',
            'Status' => ['nullable', new Enum(PropertyInvoiceEnum::class)],
        ];
    }
}
