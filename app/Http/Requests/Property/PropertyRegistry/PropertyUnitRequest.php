<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use Illuminate\Foundation\Http\FormRequest;

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
            'UnitCode' => 'required|string|max:50',
            'UnitSize' => 'required|integer',
            'IsRentable' => 'required|boolean',
            'CurrentStatus' => 'required|boolean',
            'Remarks' => 'nullable|string|max:50',
        ];
    }
}
