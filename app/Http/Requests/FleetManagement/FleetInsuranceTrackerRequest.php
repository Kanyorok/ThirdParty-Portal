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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'VehicleID' => 'required|integer|exists:t_FleetVehicles,Id',
            'InsuranceProvider' => 'required|integer|exists:t_InsuranceProviders,Id',
            'PolicyNumber' => 'required|integer',
            'CoverageStartDate' => 'required|date',
            'CoverageEndDate' => 'required|date',
            'PremiumAmount' => 'required|numeric|min:0',
            'RenewalReminderDate' => 'required|date',
            'Notes' => 'nullable|string',
            'DocumentPath' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
            'Status' => 'required|integer|exists:t_CodeDetails,ID',
            //
        ];
    }

}
