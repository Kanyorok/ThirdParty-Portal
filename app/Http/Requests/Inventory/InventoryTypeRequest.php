<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryTypeRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
   public function rules()
{
    $typeId = $this->route('inventorytype') ?? $this->route('id');
    
    return [
        'Type' => [
            'required',
            'exists:t_CodeDetails,ID',
            'string',
            'max:255',
            Rule::unique('t_InventoryTypes', 'Type')
                ->ignore($typeId, 'Id')  
                ->whereNull('DeletedOn'),
        ],
        'Status' => 'required|boolean',
    ];
}

    public function messages()
    {
        return [
            'Type.unique' => 'The Inventory Type already exists.',
        ];
    }
}
