<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRegistryRequest extends FormRequest
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
            'Model' => 'required|integer|exists:t_FleetModels,Id',
            'Make' => 'required|integer|exists:t_FleetBrands,Id',
            'Type' => 'required|integer|exists:t_CodeDetails,ID',
            'Color' => 'nullable|string|max:15',
            'Year' => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
            'ChassisNo' => 'required|string|max:500',
            'EngineNo' => 'nullable|string|max:500',

        ];
    }

}
