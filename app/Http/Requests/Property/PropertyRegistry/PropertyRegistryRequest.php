<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRegistryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            [
                'PropertyName' => 'required|string',
                'PropertyCode' => 'required|string',
                'PropertyType' => 'required|integer',
                'Category' => 'required|integer',
                'Owner' => 'required|string',
                'AcquisitionDate' => 'required|date',
                'TownCity' => 'required|integer',
                'AreaLocality' => 'required|string',
                'PropertyDescription' => 'required|string',
            ]
        ];
    }
}
