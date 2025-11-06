<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetVehicleInspectionRequest extends FormRequest
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
            'ParentInspectionID' => 'nullable|exists:t_FleetVehicleInspections,Id',
            'VehicleID'      => 'required|exists:t_FleetVehicles,Id',
            'InspectionTypeID' => 'required|exists:t_CodeDetails,Id',
            'FuelType'       => 'required|exists:t_FuelTypes,Id',
            'DriverID'       => 'required|exists:t_FleetDrivers,Id',
            'InspectionDate' => 'required|date',
            'Mileage'        => 'required|integer|min:0',
            'Fuel'           => 'required|numeric|min:0',
            'EngineOil'      => 'required|numeric|min:0',
            'Coolant'        => 'required|numeric|min:0',
            'Reflector'       => 'nullable|boolean',
            'FireExtinguisher'=> 'nullable|boolean',
            'FirstAidKit'     => 'nullable|boolean',
            'SpareTyre'       => 'nullable|boolean',
            'Spanner'         => 'nullable|boolean',
            'Jack'            => 'nullable|boolean',
            '4XFloorMats'     => 'nullable|boolean',
            'Document' => 'nullable|file|max:2048',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->VehicleID && $this->Mileage) {
                $lastInspection = \App\Models\Fleet\FleetVehicleInspection::where('VehicleID', $this->VehicleID)
                    ->orderByDesc('InspectionDate')
                    ->first();

                if ($lastInspection && $this->Mileage < $lastInspection->Mileage) {
                    $validator->errors()->add('Mileage', 'Mileage cannot be lower than the previous inspection (' . $lastInspection->Mileage . ' km).');
                }
            }
        });
    }
}