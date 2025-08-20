<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Procurement\PrequalificationStatusEnum;
use Illuminate\Validation\Rule;

class StorePrequalificationApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'round_id' => ['required', 'integer', 'exists:t_PrequalificationRounds,RoundID'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:t_SupplierCategories,SupplierCategoryID'],
            'status' => [
                'sometimes',
                'string',
                Rule::in(array_column(PrequalificationStatusEnum::cases(), 'value')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'round_id.required' => 'Please select a prequalification round.',
            'round_id.exists' => 'Selected prequalification round does not exist.',
            'category_ids.required' => 'Please select at least one supplier category.',
            'category_ids.array' => 'Categories must be submitted as an array.',
            'category_ids.min' => 'Please select at least one supplier category.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
            'status.in' => 'Invalid status value provided.',
        ];
    }
}
