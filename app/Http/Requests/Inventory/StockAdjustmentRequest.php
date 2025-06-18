<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
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
            
            'AdjustmentDate' => 'required|date',
            'Branch' => 'required|exists:t_Branches,Id',
            'Reason' => 'required|string|max:255',
            'AdjustedBy' => 'required|string|max:255',
            'Status' => 'string',
            'items' => 'required|array|min:1',
            'items.*.Item' => 'required|exists:t_Items,Id',
            'items.*.AdjustmentQty' => 'required|integer',
            'items.*.Remarks' => 'nullable|string|max:255',
        ];
    }
}
