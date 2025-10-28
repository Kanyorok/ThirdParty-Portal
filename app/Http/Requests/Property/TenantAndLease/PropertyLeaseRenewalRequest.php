<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyLeaseRenewalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'LeaseId' => ['required', 'exists:t_LeaseCreation,Id'],
            'PaymentFrequency' => ['required', 'string', 'max:50'],
            'EndDateCurrentLease' => ['required', 'date'],
            'NewStartDate' => ['required', 'date', 'after_or_equal:EndDateCurrentLease'],
            'NewEndDate' => ['required', 'date', 'after_or_equal:NewStartDate'],
            'NewMonthlyRent' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'ServiceCharge' => ['nullable', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'ParkingFee' => ['nullable', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'OtherCharges' => ['nullable', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'Remarks' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'LeaseId.required' => 'Please select a lease to renew.',
            'LeaseId.exists' => 'The selected lease does not exist.',
            'PaymentFrequency.required' => 'Payment frequency is required.',
            'EndDateCurrentLease.required' => 'End date of the current lease is required.',
            'EndDateCurrentLease.date' => 'End date of the current lease must be a valid date.',
            'NewStartDate.required' => 'New start date is required.',
            'NewStartDate.date' => 'New start date must be a valid date.',
            'NewStartDate.after_or_equal' => 'The new start date must be on or after the current lease end date.',
            'NewEndDate.required' => 'New end date is required.',
            'NewEndDate.date' => 'New end date must be a valid date.',
            'NewEndDate.after_or_equal' => 'The new end date must be on or after the new start date.',
            'NewMonthlyRent.required' => 'Please enter the new monthly rent amount.',
            'NewMonthlyRent.numeric' => 'Rent amount must be a valid number.',
            'NewMonthlyRent.regex' => 'Rent amount must be a valid number with up to two decimal places.',
            'ServiceCharge.numeric' => 'Service charge must be a valid number.',
            'ServiceCharge.regex' => 'Service charge must have up to two decimal places.',
            'ParkingFee.numeric' => 'Parking fee must be a valid number.',
            'ParkingFee.regex' => 'Parking fee must have up to two decimal places.',
            'OtherCharges.numeric' => 'Other charges must be a valid number.',
            'OtherCharges.regex' => 'Other charges must have up to two decimal places.',
            'Remarks.max' => 'Remarks cannot exceed 100 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'NewMonthlyRent' => $this->input('NewMonthlyRent') !== null ? trim($this->input('NewMonthlyRent')) : null,
            'ServiceCharge' => $this->input('ServiceCharge') !== null ? trim($this->input('ServiceCharge')) : null,
            'ParkingFee' => $this->input('ParkingFee') !== null ? trim($this->input('ParkingFee')) : null,
            'OtherCharges' => $this->input('OtherCharges') !== null ? trim($this->input('OtherCharges')) : null,
            'Remarks' => $this->input('Remarks') !== null ? trim($this->input('Remarks')) : null,
        ]);
    }
}
