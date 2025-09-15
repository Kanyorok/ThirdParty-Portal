<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyRegistryRequest extends FormRequest
{
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
                Rule::unique('t_PropertyRegistry', 'PropertyName')
                    ->ignore($this->route('Id'), 'Id') // match your route param & table PK
                    ->where(fn($query) => $query
                        ->where('PropertyType', $this->PropertyType)
                        ->where('Category', $this->Category)
                    ),
            ],
            'PropertyCode' => [
                'required',
                'string',
                'max:100',
                Rule::unique('t_PropertyRegistry', 'PropertyCode')
                    ->ignore($this->route('Id'), 'Id'), // same fix here
            ],
            'PropertyType' => 'required|exists:t_PropertyType,Id',
            'Category' => 'required|exists:t_CategoryMaster,Id',
            'Owner' => 'required|string|max:255',
            'AcquisitionDate' => 'required|date',
            'CountryId' => 'required|exists:t_Countries,Id',
            'LocationId' => 'required|exists:t_Localities,ID',
            'Address' => 'required|string|max:100',
            'PropertyDescription' => 'nullable|string|max:1000',
            'file' => 'nullable|array',
            'file.*' => 'file|max:9000',
            'IsActive' => 'nullable|boolean',
        ];
    }
}
