<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'VehicleID' => 'required|integer|exists:t_FleetVehicles,Id',
            'InsuranceProvider' => 'required|integer|exists:t_InsuranceProviders,Id',
            'PolicyNumber' => 'required|string|max:100',

            // Coverage dates
            'CoverageStartDate' => 'required|date',
            'CoverageEndDate' => 'required|date|after:CoverageStartDate',

            // Renewal reminder must not be before start date
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
            'CoverageEndDate.after' => 'The coverage Expiry date must be after the start date.',
            'RenewalReminderDate.after_or_equal' => 'The renewal reminder date cannot be before the coverage start date.',
        ];
    }
}
