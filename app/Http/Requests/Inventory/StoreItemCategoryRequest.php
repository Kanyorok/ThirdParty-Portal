<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Inventory\ItemCategories;
use Illuminate\Validation\Rule;

class StoreItemCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('create', ItemCategories::class);
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
                    ->where('ParentId', $this->ParentId),
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