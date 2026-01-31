<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use App\Models\PropertyManagement\PropertyBlock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyBlockRequest extends FormRequest
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
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockName' => [
                'required',
                'string',
                'max:50',
                Rule::unique(PropertyBlock::class, 'BlockName')
                    ->where(fn ($query) => $query->where('PropertyID', $this->PropertyID)),
            ],
            'Description' => 'nullable|string|max:100',
        ];
    }
}
