<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetDriverLicenseTrackingRequest extends FormRequest
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

            'DriverID' => 'required|exists:t_FleetDrivers,Id',
            'LicenseNumber' => 'required|string|max:50',
            'LicenseCategory' => 'nullable|string|max:50',
            'IssueDate' => 'nullable|date',
            'ExpiryDate' => 'required|date|after:IssueDate',
            'RenewalDate' => 'nullable|date|after_or_equal:ExpiryDate',
            'Notes' => 'nullable|string|max:255',
            //
        ];
    }
}
