<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class InventoryHoldReviewRequest extends FormRequest
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
            'InventoryHoldID' => 'required|exists:t_InventoryHold,Id',
            'ItemID' => 'required|exists:t_Items,Id',
            'FromBranch' => 'nullable|exists:t_Branches,Id',
            'Store' => 'nullable|exists:t_Stores,Id',
            'Quantity' => 'required|numeric|min:0.01',
            'Defect' => 'nullable|string|max:255',
            'Condition' => 'nullable|string|max:255',
            'Status' => 'nullable|string|max:50',
            'Notes' => 'nullable|string',
        ];
    }
}


