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
            'Phone' => ['nullable','string','max:20','regex:/^\+[1-9]\d{7,14}$/'],
            'CompanyID' => 'required|integer|exists:t_ThirdParties,Id',
            'ContractStartDate' => 'nullable|date',
            'ContractEndDate' => 'nullable|date|after_or_equal:ContractStartDate',
            'Notes' => 'nullable|string|max:1000',
            'IsActive' => 'required|boolean',
            'Document' => 'nullable|file|max:2048',
            //
        ];
    }
}
