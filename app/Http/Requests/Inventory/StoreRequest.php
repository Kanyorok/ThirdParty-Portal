<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
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
            'StoreName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('t_Stores', 'StoreName')->ignore($this->route('Id')),
            ],
            'BranchID' => 'required|integer|exists:t_Branches,Id',
            'Status' => 'required|boolean',
        ];

    }

    public function messages()
    {
        return [
            'StoreName.unique' => 'The Store Name already exists.',
        ];
    }
}

