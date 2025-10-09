<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class VehicleManagementRequest extends FormRequest
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
            'RegistrationNo' => 'required|string|max:255',
            'VehicleType' => 'required|integer|exists:t_CodeDetails,ID',
            'Make' => 'nullable|integer|exists:t_FleetBrands,Id',
            'Model' => 'nullable|integer|exists:t_FleetModels,Id',
            'YearOfManufacture' => 'nullable|integer',
            'ChassisNo' => 'nullable|string|max:100',
            'EngineNo' => 'nullable|string|max:100',
            'FuelType' => 'required|integer|exists:t_FuelTypes,Id',
            'Capacity' => 'nullable|string|max:50',
            'OdometerReading' => 'nullable|numeric',
            'Status' => 'required|integer|exists:t_CodeDetails,ID',
            'VehicleStatus' => 'nullable|integer|exists:t_CodeDetails,ID',
            'MaxLoad' => 'nullable|numeric',
            'MaxPassengers' => 'nullable|integer',
            'Color' => 'nullable|string|max:15',
            'ImageFile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'AssignedBranch' => 'required|integer|exists:t_Branches,Id',
            //
        ];
    }
}
