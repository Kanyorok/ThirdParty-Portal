<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'ItemID' => 'required|exists:t_Items,Id',
            'UOM' => 'nullable|exists:t_UOM,Id',
            'UnitCost' => 'nullable|numeric|min:0',
            'Store' => 'required|exists:t_Stores,Id',
            'Branch' => 'required|integer|exists:t_Branches,Id',
            'CurrentQty' => 'required|integer|min:1',
            'Min' => 'required|integer|min:0',
            'Reorder' => 'required|integer|min:0',
            'LastReceived' => ['required', 'date', 'before_or_equal:today'],
            'Status' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'ItemID.required' => 'Item field is required.',
            'Store.required' => 'Store field is required.',
            'CurrentQty.required' => 'Current Qty field is required.',
        ];
    }
}
