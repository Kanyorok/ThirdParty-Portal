<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use App\Models\PropertyManagement\PropertyUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyUnitRequest extends FormRequest
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
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockID' => 'required|exists:t_PropertyBlock,Id',
            'FloorID' => 'required|exists:t_PropertyFloor,Id',
            'UnitCode' => [
                'required',
                'string',
                'max:50',
                'min:1',
                Rule::unique((new PropertyUnit())->getTable())
                    ->where(
                        fn ($query) => $query
                        ->where('PropertyID', $this->PropertyID)
                        ->where('BlockID', $this->BlockID)
                        ->where('FloorID', $this->FloorID)
                    )
                    ->ignore($this->route('id'), 'Id'),
            ],
            'UnitSize' => 'required|integer|min:1',
            'IsRentable' => 'required|boolean',
            'CurrentStatus' => 'required|boolean',
            'Remarks' => 'nullable|string|max:50',
        ];
    }
}
