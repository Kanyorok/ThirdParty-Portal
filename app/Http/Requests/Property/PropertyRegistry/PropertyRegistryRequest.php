<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
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
                Rule::unique(PropertyRegistry::class, 'PropertyName')
                    ->ignore($this->route('Id'), 'Id')
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
                    ->ignore($this->route('Id'), 'Id'),
            ],
            'PropertyType' => 'required|exists:t_PropertyType,Id',
            'Category' => 'required|exists:t_CategoryMaster,Id',
            'Owner' => 'required|string|max:255',
            'AcquisitionDate' => 'required|date|before_or_equal:today',
            'CountryId' => 'required|exists:t_Countries,Id',
            'LocationId' => 'required|exists:t_Localities,ID',
            'Address' => 'required|string|max:100',
            'PropertyDescription' => 'nullable|string|max:1000',
            'file' => 'nullable|array',
            'file.*' => 'file|max:25000',
            'IsActive' => 'nullable|boolean',
        ];
    }

    /**
     * Add custom validation after the normal rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Only run if updating an existing property
            $propertyId = $this->route('Id');
            if (!$propertyId) {
                return;
            }

            $property = PropertyRegistry::find($propertyId);

            if (!$property) {
                return;
            }

            // Check if deactivating while units are occupied
            if ($this->boolean('IsActive') === false) {
                $hasOccupied = PropertyUnit::where('PropertyID', $property->Id)
                    ->where('CurrentStatus', false) // assuming false = occupied
                    ->exists();

                if ($hasOccupied) {
                    $validator->errors()->add(
                        'IsActive',
                        'You cannot deactivate this property because one or more units are currently occupied.'
                    );
                }
            }
        });
    }
}
