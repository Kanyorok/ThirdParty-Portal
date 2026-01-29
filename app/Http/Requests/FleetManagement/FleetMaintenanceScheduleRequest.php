<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetMaintenanceScheduleRequest extends FormRequest
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
            'VehicleID' => 'required|exists:t_FleetVehicles,Id',
            'MaintenanceType' => 'required|exists:t_CodeDetails,ID',
            'ScheduledDate' => 'required|date',
            'ScheduledMileage' => [
                'nullable',
                'integer',
                'min:0',
                // Only allow mileage on update
                function ($attribute, $value, $fail) {
                    if ($this->isMethod('POST') && ! is_null($value)) {
                        $fail('Scheduled mileage can only be set when editing a record.');
                    }
                },
            ],
            'VendorID' => 'nullable|exists:t_SupplierMaster,Id',
            'Notes' => 'nullable|string',
            'Status' => 'nullable|boolean',
        ];
    }
}
