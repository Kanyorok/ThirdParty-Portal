<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FuelTypRequest extends FormRequest
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
     */
    public function rules(): array
    {
        // Detect if this is an update request (e.g. /fueltypes/{Id})
        $fuelTypeId = $this->route('Id') ?? $this->route('id') ?? $this->route('fueltype') ?? null;

        $rules = [
            'Description' => 'nullable|string|max:255',
            'IsActive'    => 'boolean',
        ];

        // 🔹 For creation (no ID in route)
        if (!$fuelTypeId) {
            $rules['FuelName'] = [
                'required',
                'string',
                'max:100',
                'unique:t_FuelTypes,FuelName',
            ];
        }
        // 🔹 For update (ID exists in route)
        else {
            $rules['FuelName'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('t_FuelTypes', 'FuelName')->ignore($fuelTypeId, 'Id'),
            ];
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'FuelName.required' => '⛽ Please enter a fuel name.',
            'FuelName.unique'   => '⚠️ This fuel name is already in use.',
            'FuelName.max'      => '📝 The fuel name may not exceed 100 characters.',
            'IsActive.boolean'  => '✅ The "Is Active" field must be true or false.',
        ];
    }
}
