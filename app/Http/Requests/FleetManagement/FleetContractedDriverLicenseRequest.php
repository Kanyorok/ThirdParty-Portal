<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetContractedDriverLicenseRequest extends FormRequest
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
            'ContractedDriverID' => 'required|integer|exists:t_ContractedDrivers,Id',
            'LicenseNumber' => 'required|string|max:50',
            'LicenseCategory' => 'required|string|max:20',
            'IssueDate' => 'required|date',
            'ExpiryDate' => 'required|date|after_or_equal:IssueDate',
            'Notes' => 'nullable|string|max:255',
            //
        ];
    }
}
