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
            'StaffNumber' => 'required|integer|exists:t_HREmployees,Id',
            'NationalID' => 'required|integer',
            'Phone' => ['required','string','max:20','regex:/^\+[1-9]\d{7,14}$/'],
            'Email' => 'required|string',
            'EmploymentType' => 'required|integer|exists:t_CodeDetails,ID',
            'Notes' => 'nullable|string',
            'IsActive' => 'required|boolean',
            'Document' => 'nullable|file|max:2048',
            'DriverStatus' => 'nullable|numeric|exists:t_CodeDetails,ID',
            'ImageFile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
