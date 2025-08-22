<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class ContractedDriversRequest extends FormRequest
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
            'FullName' => 'required|string|max:255',
            'NationalID' => 'nullable|string|max:50',
            'Phone' => 'nullable|string|max:50',
            'CompanyName' => 'nullable|string|max:255',
            'ContractStartDate' => 'nullable|date',
            'ContractEndDate' => 'nullable|date|after_or_equal:ContractStartDate',
            'LicenseNumber' => 'nullable|string|max:100',
            'Notes' => 'nullable|string|max:1000',
            'IsActive' => 'required|boolean',
            //
        ];
    }
}
