<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventoryTypeRequest extends FormRequest
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


    public function rules()
    {
        return [
            'Type' => [
                'required',
                'string',
                'max:255',
                Rule::unique('t_InventoryTypes', 'Type')->ignore($this->route('Id')),
            ],
            'Status' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'Type.unique' => 'The Inventory Type already exists.',
        ];
    }


}
