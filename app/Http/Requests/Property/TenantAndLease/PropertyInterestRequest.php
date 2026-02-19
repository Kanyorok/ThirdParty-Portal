<?php

namespace App\Http\Requests\Property\TenantAndLease;

use Illuminate\Foundation\Http\FormRequest;

class PropertyInterestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'PropertyId' => 'required|exists:t_PropertyRegistry,Id',
            'BlockId' => 'required|exists:t_PropertyBlock,Id',
            'FloorId' => 'required|exists:t_PropertyFloor,Id',
            'UnitId' => 'required|exists:t_PropertyUnit,Id',
            'TenantId' => 'required|exists:t_TenantMaintenance,Id',

            'InterestedStartDate' => 'required|date|after_or_equal:today',
            'InterestedEndDate' => 'required|date|after_or_equal:InterestedStartDate',

            'PaymentFrequency' => 'required|exists:t_CodeDetails,ID',

            'AdditionalInformation' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'PropertyId.required' => 'Please select a property.',
            'BlockId.required' => 'Please select a block.',
            'FloorId.required' => 'Please select a floor.',
            'UnitId.required' => 'Please select a unit.',
            'TenantId.required' => 'Please select a tenant.',
            'InterestedStartDate.required' => 'Start date is required.',
            'InterestedEndDate.required' => 'End date must be after start date.',
            'PaymentFrequency.required' => 'Select payment frequency.',
        ];
    }
}
