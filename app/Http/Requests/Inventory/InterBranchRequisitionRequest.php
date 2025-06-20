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
            'items.*.Item' => 'required|exists:t_Items,Id',
            'items.*.ItemCode' => 'required|exists:t_Items,ItemCode',
            'items.*.RequestedQty' => 'required|integer|min:1',
            'items.*.Remarks' => 'nullable|string|max:255',
        ];
    }
}