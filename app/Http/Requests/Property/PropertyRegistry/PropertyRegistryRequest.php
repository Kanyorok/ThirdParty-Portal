<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        'PropertyName' => [
            'required',
            'string',
            'max:255',
            Rule::unique('t_PropertyRegistry')
                ->where(function ($query) {
                    return $query
                        ->where('PropertyType', $this->PropertyType)
                        ->where('Category', $this->Category);
                })
                ->ignore($this->route('id')),
        ],
        'PropertyCode' => 'required|string|max:100',
        'PropertyType' => 'required|exists:t_PropertyType,Id',
        'Category' => 'required|exists:t_CategoryMaster,Id',
        'Owner' => 'required|string|max:255',
        'AcquisitionDate' => 'required|date',
        'Country' => 'required|string|max:100',
        'TownCity' => 'required|exists:t_Localities,ID',
        'AreaLocality' => 'required|string|max:100',
        'PropertyDescription' => 'nullable|string|max:1000',
        'file' => 'nullable|array',
        'file.*' => 'file|max:9000',
        'IsActive' => 'nullable|boolean',
    ];
}

}
