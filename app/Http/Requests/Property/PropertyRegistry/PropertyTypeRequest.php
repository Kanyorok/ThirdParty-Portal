<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use App\Models\PropertyManagement\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyTypeRequest extends FormRequest
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
            'PropertyTypeName' => [
                'required',
                'string',
                Rule::unique(PropertyType::class, 'PropertyTypeName')
                    ->where(fn ($query) => $query->where('PropertyCategoryId', $this->PropertyCategoryId)),
            ],
            'PropertyCategoryId' => 'required|exists:t_CategoryMaster,Id',
            'Description' => 'nullable|string|max:255',
        ];
    }
}
