<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyLeaseRenewalRequest extends FormRequest
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
            'LeaseId' => 'required|exists:t_LeaseCreation,Id',
            'PaymentFrequency' => 'required|string|max:50',
            'EndDateCurrentLease' => 'required|date',
            'NewStartDate' => 'required|date',
            'NewEndDate' => 'required|date',
            'NewMonthlyRent' => 'required|integer',
            'ServiceCharge' =>  'nullable|integer',
            'ParkingFee'    =>  'nullable|integer',
            'OtherCharges'  =>  'nullable|integer',
            'Remarks' => 'nullable|string|max:100',
        ];
    }
}
