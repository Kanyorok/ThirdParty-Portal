<?php

namespace App\Http\Requests\Procurement\Suppliers;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'IsActive' => $this->has('IsActive') ? 1 : 0,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'CategoryName' => ['required', 'string', 'max:200', 'unique:t_SupplierCategories,CategoryName',],
            'Description' => ['nullable', 'string', 'max:500'],
            'IsActive' => ['boolean'],
            'item_category_ids' => ['array'],
            'item_category_ids.*' => ['integer', 'distinct', 'exists:t_ItemCategories,Id'],
        ];
    }

    public function attributes()
    {
        return [
            'CategoryName' => 'Category Name',
            'Description' => 'Category Description',
            'IsActive' => 'Active Status',
        ];
    }
}
