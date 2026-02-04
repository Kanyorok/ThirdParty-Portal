<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitOfMeasureRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'Code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('t_UOM', 'Code')
                    ->ignore($this->route('Id'))
                    ->whereNull('DeletedOn'),
            ],
            'Name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('t_UOM', 'Name')
                    ->ignore($this->route('Id'))
                    ->whereNull('DeletedOn'),
            ],
            'BaseUnit' => 'nullable|boolean',
            'Active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'Code.unique' => 'The Unit of Measure code already exists.',
            'Name.unique' => 'The Unit of Measure name already exists.',
        ];
    }
}
