<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vehicleId = $this->route('Id'); // use your route parameter (update mode)

        $rules = [
            'ChassisNo' => 'nullable|string',
            'VehicleType' => 'required|integer|exists:t_CodeDetails,ID',
            'Make' => 'nullable|integer|exists:t_FleetBrands,Id',
            'Model' => 'nullable|integer|exists:t_FleetModels,Id',
            'YearOfManufacture' => 'nullable|integer',
            'FuelType' => 'required|integer|exists:t_FuelTypes,Id',
            'Capacity' => 'nullable|string|max:50',
            'OdometerReading' => 'nullable|numeric',
            'Status' => 'required|integer|exists:t_CodeDetails,ID',
            'MaxLoad' => 'nullable|numeric',
            'MaxPassengers' => 'nullable|integer',
            'Color' => 'nullable|string|max:15',
            'ImageFile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'AssignedBranch' => 'required|integer|exists:t_Branches,Id',
            'VehicleStatus' => 'nullable|string',
        ];

        // ✅ For creation
        if (! $vehicleId) {
            $rules['RegistrationNo'] = [
                'required',
                'string',
                'max:255',
                'unique:t_FleetVehicles,RegistrationNo',
            ];
            $rules['ChassisNo'] = [
                'nullable',
                'string',
                'max:100',
                'unique:t_FleetVehicles,ChassisNo',
            ];
            $rules['EngineNo'] = [
                'nullable',
                'string',
                'max:100',
                'unique:t_FleetVehicles,EngineNo',
            ];
        } else {
            // ✅ For update
            $rules['RegistrationNo'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('t_FleetVehicles', 'RegistrationNo')->ignore($vehicleId, 'Id'),
            ];
            $rules['ChassisNo'] = [
                'nullable',
                'string',
                'max:100',
                Rule::unique('t_FleetVehicles', 'ChassisNo')->ignore($vehicleId, 'Id'),
            ];
            $rules['EngineNo'] = [
                'nullable',
                'string',
                'max:100',
                Rule::unique('t_FleetVehicles', 'EngineNo')->ignore($vehicleId, 'Id'),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'RegistrationNo.unique' => '⚠️ A vehicle with this registration number already exists.',
            'ChassisNo.unique' => '⚙️ The chassis number has already been taken.',
            'EngineNo.unique' => '🚫 A vehicle with this engine number already exists.',
            'FuelType.required' => '⛽ The fuel type field is required.',
        ];
    }
}
