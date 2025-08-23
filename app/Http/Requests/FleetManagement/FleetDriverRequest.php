<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetDriverRequest extends FormRequest
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
            'FullName' => 'required|string',
            'StaffNumber' => 'required|integer|exists:t_Employees,Id',
            'NationalID' => 'required|integer',
            'Phone' => 'required|string',
            'LicenseNumber' => 'required|string',
            'LicenseExpiryDate' => 'required|date',
            'EmploymentType' => 'required|integer|exists:t_CodeDetails,ID',
            'Notes' => 'required|string',
            'IsActive' => 'required|boolean',
        ];
    }

}
