<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class StockItemRequest extends FormRequest
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
    public function rules()
    {
        return [
            //'Batch' => 'required|boolean',
            'ItemID' => 'required|exists:t_Items,Id',
            'UOM' => 'nullable|exists:t_UOM,Id',
            'UnitCost' => 'nullable|numeric|min:0',
            // 'Serial' => 'required|boolean',
            // 'Perishable' => 'required|boolean',
            // 'Saleable' => 'required|boolean',
            // 'Purchasable' => 'required|boolean',
            'Store' => 'nullable|exists:t_Stores,Id', 
            'Branch' => 'required|integer|exists:t_Branches,Id',
            'CurrentQty' => 'required|integer|min:0',
            'Min' => 'required|integer|min:0',
            'Reorder' => 'required|integer|min:0',
            'LastReceived' => ['required', 'date', 'before_or_equal:today'],
            'Status' => 'nullable|boolean',
        ];
    }
}