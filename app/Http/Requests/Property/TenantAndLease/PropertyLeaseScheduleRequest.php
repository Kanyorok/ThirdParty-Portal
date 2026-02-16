<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyLeaseScheduleRequest extends FormRequest
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
            'LeaseId' => 'required|exists:t_LeaseCreation,Id',
            'PaymentFrequency' => 'required|exists:t_CodeDetails,ID',
            'StartDate' => 'required|after_or_equal:today',
            'EndDate' => 'required|date|after_or_equal:StartDate',
            'BaseRent' => 'required|numeric|min:0',
            'ServiceCharge' => 'required|numeric|min:0',
            'ParkingFee' => 'required|numeric|min:0',
            'OtherCharges' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'LeaseId.required' => 'Please select a lease.',
            'PaymentFrequency.required' => 'Payment frequency is required.',
            'StartDate.required' => 'Start date is required.',
            'EndDate.required' => 'End date is required.',
            'EndDate.after_or_equal' => 'End date must be on or after the start date.',
            'BaseRent.required' => 'Base rent is required.',
            'ServiceCharge.required' => 'Service charge is required.',
            'ParkingFee.required' => 'Parking fee is required.',
            'OtherCharges.required' => 'Other charges is required.',
        ];
    }
}
