<?php

namespace App\Http\Requests\Inventory;

use App\Models\Inventory\ItemCategories;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = ItemCategories::findOrFail($this->route('id'));

        return $this->user()->can('update', $category);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('t_ItemCategories', 'Name')
                    ->ignore($this->route('id'))
                    ->where('ParentId', $this->ParentId)
                    ->whereNull('DeletedOn'),
            ],
            'Description' => 'nullable|string',
            'ParentId' => 'nullable|exists:t_ItemCategories,Id',
            'Status' => 'nullable|exists:t_CodeDetails,ID',
            'ItemTypeId' => 'nullable|integer|exists:t_ItemTypes,Id',
        ];
    }

    public function messages(): array
    {
        return [
            'Name.unique' => 'The Item Category name already exists.',
        ];
    }
}
