<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyNewLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'InterestId' => 'nullable|exists:t_PropertyInterest,Id',
            'Tenant' => 'required|exists:t_TenantMaintenance,Id',
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockID' => 'required|exists:t_PropertyBlock,Id',
            'FloorID' => 'required|exists:t_PropertyFloor,Id',
            'Unit' => 'required|exists:t_PropertyUnit,Id',

            'StartDate' => 'required|date|after_or_equal:today',
            'EndDate' => 'required|date|after_or_equal:StartDate',

            'PaymentFrequency' => 'required|exists:t_CodeDetails,ID',
            'MonthlyRent' => 'required|numeric|min:0',
            'Deposit' => 'required|numeric|min:0',
            'ServiceCharge' => 'required|numeric|min:0',
            'ParkingFee' => 'required|numeric|min:0',
            'OtherCharges' => 'required|numeric|min:0',

            'DueDay' => 'required|integer|between:1,28',
            'SpecialTerms' => 'nullable|string|max:255',

            'TaxId' => 'required|exists:t_FinanceTaxRuleConfiguration,Id',
            'CurrencyId' => 'required|exists:t_Currencies,Id',

            'Document' => 'nullable|array',
            'Document.*' => 'file|max:9000',
        ];
    }

    protected function prepareForValidation(): void
    {
        $fields = [
            'MonthlyRent',
            'Deposit',
            'ServiceCharge',
            'ParkingFee',
            'OtherCharges',
        ];

        $sanitized = [];

        foreach ($fields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                if (is_string($value)) {
                    $value = str_replace([',', ' '], ['', ''], $value);
                    $value = trim($value);
                }
                $sanitized[$field] = $value;
            }
        }

        if (!empty($sanitized)) {
            $this->merge($sanitized);
        }
    }

    public function messages(): array
    {
        return [
            'Tenant.required' => 'Please select a tenant.',
            'PropertyID.required' => 'Please select a property.',
            'BlockID.required' => 'Please select a block.',
            'FloorID.required' => 'Please select a floor.',
            'Unit.required' => 'Please select a unit.',
            'StartDate' => 'Start date must be current or future date.',
            'EndDate' => 'End date must be on or after the start date.',
            'PaymentFrequency.required' => 'Please select a payment frequency.',
            'MonthlyRent.required' => 'Monthly rent is required.',
            'MonthlyRent.numeric' => 'Monthly rent must be a number.',
            'Deposit.required' => 'Deposit amount is required.',
            'Deposit.numeric' => 'Deposit must be a number.',
            'ServiceCharge' => 'Service Charge amount is required.',
            'ServiceCharge.numeric' => 'Service Charge must be a number.',
            'ParkingFee' => 'Parking Fee amount is required.',
            'ParkingFee.numeric' => 'Parking Fee must be a number.',
            'OtherCharges' => 'Other Charges amount is required.',
            'OtherCharges.numeric' => 'Other Charges must be a number.',
            'DueDay.required' => 'Due day is required.',
            'DueDay.between' => 'Due day must be between 1 and 28.',
            'SpecialTerms.max' => 'Special terms must not exceed 255 characters.',
            'Document.file' => 'The document must be a file.',
        ];
    }
}
