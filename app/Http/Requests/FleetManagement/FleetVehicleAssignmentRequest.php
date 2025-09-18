<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetVehicleAssignmentRequest extends FormRequest
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
        'TripNo' => 'nullable|exists:t_TripLogs,Id',
        'VehicleType' => 'nullable|string|exists:t_CodeDetails,ID',
        'VehicleID' => 'nullable|string|exists:t_FleetVehicles,Id',
        'DriverID' => 'nullable|integer',
        'AssignedBy' => 'nullable|integer|exists:t_Employees,Id',
        'LastInspectionDate' => 'nullable|date',
        'AssignmentDate' => 'required|date',
        'Purpose' => 'nullable|string',
        'Notes' => 'nullable|string',

        
        ];
    }
}
