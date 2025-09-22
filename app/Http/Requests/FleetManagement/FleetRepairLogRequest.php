<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetRepairLogRequest extends FormRequest
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
            'RepairType' => 'required|integer',
            'RepairDate' => 'required|date',
            'Vendor' => 'nullable|string|max:255',
            'Cost' => 'nullable|numeric|min:0',
            'Description' => 'nullable|string|max:1000',
            'Notes' => 'nullable|string',
            'ScheduleID' => 'nullable|exists:t_FleetMaintenanceSchedules,Id'
        ];
    }
}
