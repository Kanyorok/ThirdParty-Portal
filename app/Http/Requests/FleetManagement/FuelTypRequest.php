<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Fleet\FuelType;

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
            $fuelTypeId = $this->route('Id') ?? $this->route('id') ?? $this->route('fueltype') ?? null;

            $rules = [
                'Description' => 'nullable|string|max:255',
                'IsActive'    => 'boolean',
            ];

            if (!$fuelTypeId) {
                $rules['FuelName'] = [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('t_FuelTypes', 'FuelName')->where(function ($query) {
                        return $query->whereNull('DeletedOn');
                    }),
                ];
            }
            else {
                $rules['FuelName'] = [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('t_FuelTypes', 'FuelName')
                        ->where(function ($query) {
                            return $query->whereNull('DeletedOn');
                        })
                        ->ignore($fuelTypeId, 'Id'),
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