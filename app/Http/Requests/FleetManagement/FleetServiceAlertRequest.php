<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetServiceAlertRequest extends FormRequest
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
        'ScheduleID'      => 'required|exists:t_FleetMaintenanceSchedules,Id',
        'VehicleID'      => 'required|exists:t_FleetVehicles,Id',
        'AlertType'      => 'required|exists:t_CodeDetails,Id',
        'Description'    => 'nullable|string|max:1000',
        'TriggerMileage' => 'nullable|numeric',
        'TriggerDate'    => 'nullable|date',
        'IsAcknowledged' => 'boolean',
        'AcknowledgedOn' => 'nullable|date',
        'AcknowledgedBy' => 'nullable|exists:t_Users,Id',
        'CreatedBy'      => 'nullable|exists:t_Users,Id',
        'CreatedOn'      => 'nullable|date',
        'ModifiedBy'     => 'nullable|exists:t_Users,Id',
        'ModifiedOn'     => 'nullable|date',
        'DeletedBy'      => 'nullable|exists:t_Users,Id',
        'DeletedOn'      => 'nullable|date',
    ];
}


}
