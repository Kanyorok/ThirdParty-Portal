<?php

namespace App\Http\Requests\Insurance\Customers;

use Illuminate\Foundation\Http\FormRequest;

class BancassuranceCustomersContactsRequest extends FormRequest
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
            'ContactDate' => 'required|date|max:100',
            'ContactType' => 'required|exists:t_CodeDetails,ID',
            'Summary' => 'required|string|max:100',
            'HandledBy' => 'required|nullable|exists:t_HREmployees,Id',
            'Notes' => 'required|string|max:100',
        ];
    }
}
