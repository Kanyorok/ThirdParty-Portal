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
            'TenantId'=>'required|exists:t_LeaseCreation,Id',
            'Lease' => 'required|exists:t_LeaseCreation,Id',
            'BillingMonth' => 'required|string|max:50',
            'InvoiceDate' => 'required|date',
            'RentAmount' => 'required|integer',
            'ServicesCharge' => 'required|integer',
            'OtherCharges' => 'required|integer',
            'InvoiceNotes' => 'nullable|string|max:100',
        ];
    }
}
