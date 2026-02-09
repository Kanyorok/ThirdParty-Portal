<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class TransactionTransferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

public function rules()
{
    $rules = [
        'RequisitionType' => 'required|string|in:interbranch,procurement',
        'TransferDate' => 'required|date',
        'TransferredBy' => 'required|string',
        'FromBranch' => 'nullable|exists:t_Branches,Id',
        'ToBranch' => 'required|exists:t_Branches,Id',

        'items' => 'required|array|min:1',
        'items.*.item' => 'required|exists:t_Items,Id',
        'items.*.unit_cost' => 'required|numeric|min:0',
        'items.*.uom' => 'required|exists:t_UOM,Id',
        'items.*.approved_qty' => 'required|numeric|min:1',
        'items.*.dispatched_qty' => 'required|numeric|min:0',
        'items.*.remarks' => 'nullable|string|max:255',
    ];

    // Add batch_allocation validation for non-HQ users
    // This should be handled dynamically based on user's branch
    $rules['items.*.batch_allocation'] = 'nullable|array';
    $rules['items.*.batch_allocation.*.ledger_id'] = 'required|exists:t_StockGRNLedger,Id';
    $rules['items.*.batch_allocation.*.quantity'] = 'required|numeric|min:0';

    if ($this->input('RequisitionType') === 'procurement') {
        $rules['RequisitionId'] = 'required|exists:t_GoodsReceipts,Id';
    } else {
        $rules['RequisitionId'] = 'required|exists:t_InterBranchRequisition,Id';
    }

    return $rules;
}
}
