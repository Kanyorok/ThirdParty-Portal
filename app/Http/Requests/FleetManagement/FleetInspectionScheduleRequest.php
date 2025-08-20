<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetInspectionScheduleRequest extends FormRequest
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
            
            'VehicleID' => 'required|integer|exists:t_FleetVehicles,Id',
            'InspectionType' => 'required|string',
            'InspectionDate' => 'required|date',
            'DueDate' => 'required|date',
            'Inspector'=> 'required|integer|exists:t_Employees,Id',
            'Status' => 'required|integer|exists:t_CodeDetails,ID',
            'Remarks' => 'nullable|string',
            //
        ];
    }

}
