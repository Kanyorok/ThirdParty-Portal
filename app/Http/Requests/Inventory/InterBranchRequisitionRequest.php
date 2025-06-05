<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class InterBranchRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'FromBranch' => 'required|exists:t_Branches,Id',
            'ToBranch' => 'required|exists:t_Branches,Id|different:FromBranch',
            'CreatedOn' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.Category' => 'required|exists:t_ItemCategories,Id',
            'items.*.Subcategory' => 'nullable|exists:t_ItemCategories,Id',
            'items.*.Item' => 'required|exists:t_Items,Id',
            'items.*.UOM' => 'required|exists:t_UOM,Id',
            'items.*.RequestedQty' => 'required|integer|min:1',
            'items.*.Remarks' => 'nullable|string|max:255',
        ];
    }
}