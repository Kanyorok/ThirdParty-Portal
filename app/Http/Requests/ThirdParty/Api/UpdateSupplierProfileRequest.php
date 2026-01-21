<?php

namespace App\Http\Requests\ThirdParty\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:t_SupplierCategories,SupplierCategoryID'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_ids.*.exists' => 'One or more selected categories are invalid',
        ];
    }
}
