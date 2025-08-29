<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetDriverAssignmentRequest extends FormRequest
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

            'DriverID' => 'required|exists:t_FleetDrivers,Id',
            'VehicleID' => 'required|exists:t_FleetVehicles,Id',
            'AssignmentDate' => 'required|date',
            'UnassignmentDate' => 'nullable|date|after_or_equal:AssignmentDate',
            'Purpose' => 'nullable|string|max:255',
            'AssignedBy' => 'required|exists:t_Employees,Id',
            'Notes' => 'nullable|string',


        ];
    }
}
