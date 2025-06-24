<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyNewLeaseRequest extends FormRequest
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
            'Tenant' => 'required|exists:t_TenantMaintenance,Id',
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockID' => 'required|exists:t_PropertyRegistry,Id',
            'FloorID' => 'required|exists:t_PropertyFloor,Id',
            'Unit' => 'required|exists:t_PropertyUnit,Id',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date',
            'PaymentFrequency' => 'required|exists:t_CodeDetails,ID',
            'MonthlyRent' => 'required|numeric|min:0',
            'Deposit' => 'required|numeric|min:0',
            'DueDay' => 'required|integer',
            'SpecialTerms' => 'nullable|string|max:255',
        ];
    }
}
