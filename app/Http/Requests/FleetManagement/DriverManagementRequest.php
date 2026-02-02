<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class DriverManagementRequest extends FormRequest
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

            'DriverName' => 'required|string|max:255',
            'LicenseNumber' => 'required|string|max:50|',
            'LicenseExpiryDate' => 'required|date',
            'EmploymentStatus' => 'required|numeric|exists:t_CodeDetails,ID',
            'Phone' => ['nullable','string','max:20','regex:/^\+[1-9]\d{7,14}$/'],
            'Email' => 'nullable|string|max:255',
            'Remarks' => 'nullable|string|max:500',

        ];
    }
}
