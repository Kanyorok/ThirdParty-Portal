<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemTypeRequest extends FormRequest
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
            'TypeName' => ['required', 'exists:t_CodeDetails,ID',
                'string',
                'max:255',
                Rule::unique('t_ItemTypes', 'TypeName')->ignore($this->route('Id'))->whereNull('DeletedOn'),
            ],
            'StockTracked' => 'required|boolean',
            'RequiresTagging' => 'required|boolean',
            'Active' => 'nullable|boolean',
        ];

    }


    public function messages()
    {
        return [
            'TypeName.unique' => 'The Item Type already exists.',
        ];
    }

}
