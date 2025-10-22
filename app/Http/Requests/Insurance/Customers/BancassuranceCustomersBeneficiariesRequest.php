<?php

namespace App\Http\Requests\Insurance\Customers;

use Illuminate\Foundation\Http\FormRequest;

class BancassuranceCustomersBeneficiariesRequest extends FormRequest
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
            'CustomerID' => 'required|exists:t_BancassuranceCustomers,Id',
            'PolicyID' => 'required|nullable|exists:t_BancassurancePolicies,Id',
            'FullName' => 'required|string|max:255',
            'Relationship' => 'nullable|exists:t_CodeDetails,ID',
            'IDNumber' => 'nullable|string|max:50',
            'Phone' => ['nullable','string','max:20','regex:/^\+[1-9]\d{7,14}$/'],
            'Email' => 'nullable|email|max:100',
            'PercentageShare' => 'required|numeric|min:0|max:100',
            'IsPrimary' => 'nullable|boolean',
        ];
    }
}
