<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyLeaseScheduleRequest extends FormRequest
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
            'LeaseNumber' => 'required|exists:t_LeaseCreation,Id',
            'TenantId' => 'required|exists:t_LeaseCreation,Id',
            'PropertyId' => 'required|exists:t_LeaseCreation,Id',
            'PaymentFrequency' => 'required|string|max:50',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date',
            'BaseRent' => 'required|numeric',
            'ServiceCharge' => 'required|numeric',
            'ParkingFee' => 'required|numeric',
            'OtherCharges' => 'required|numeric',
        ];
    }
}
