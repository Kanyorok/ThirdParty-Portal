<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateItemCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $category = ItemCategories::findOrFail($this->route('id'));
        return auth()->user()->can('update', $category);
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
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'Name.unique' => 'The Item Category name already exists.',
        ];
    }
}