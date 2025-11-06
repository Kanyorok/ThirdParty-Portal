<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FleetInsuranceTrackerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        // Handle both 'id' and 'Id' route parameter names
        $insuranceId = $this->route('id') ?? $this->route('Id');

        return [
            'VehicleID' => 'required|integer|exists:t_FleetVehicles,Id',
            'InsuranceProvider' => 'required|integer|exists:t_InsuranceProviders,Id',

            // ✅ Enforce unique PolicyNumber on create, ignore on update, ignore soft-deleted
            'PolicyNumber' => [
                'required',
                'string',
                'max:100',
                Rule::unique('t_FleetInsuranceTracker', 'PolicyNumber')
                    ->whereNull('DeletedOn')
                    ->ignore($insuranceId, 'Id'),
            ],

            // ✅ Date rules
            'CoverageStartDate' => 'required|date',
            'CoverageEndDate' => 'required|date|after:CoverageStartDate',

            // ✅ Renewal reminder must not be before start
            'RenewalReminderDate' => 'nullable|date|after_or_equal:CoverageStartDate',

            'PremiumAmount' => 'required|numeric|min:0',
            'Notes' => 'nullable|string',
            'Document' => 'nullable|file|max:2048',
            'Status' => 'required|integer|exists:t_CodeDetails,ID',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'VehicleID.required' => ' Please select a vehicle.',
            'InsuranceProvider.required' => 'Please select an insurance provider.',
            'PolicyNumber.required' => 'The policy number is required.',
            'PolicyNumber.unique' => 'This policy number already exists. Please use a unique one.',
            'CoverageStartDate.required' => 'The coverage start date is required.',
            'CoverageEndDate.required' => 'The coverage end date is required.',
            'CoverageEndDate.after' => 'The coverage expiry date must be after the start date.',
            'RenewalReminderDate.after_or_equal' => 'The renewal reminder date cannot be before the start date.',
            'PremiumAmount.required' => 'Please enter the premium amount.',
            'PremiumAmount.numeric' => 'Premium amount must be a valid number.',
        ];
    }
}
