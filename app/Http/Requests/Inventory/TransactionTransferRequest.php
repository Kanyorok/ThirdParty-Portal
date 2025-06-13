<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class TransactionTransferRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

public function rules()
{
    return [
        'TransferDate' => 'required|date',
        'TransferredBy' => 'required|string',
        'FromBranch' => 'required|exists:t_Branches,Id',
        'ToBranch' => 'required|exists:t_Branches,Id',
        'items' => 'required|array|min:1',
        'items.*.item' => 'required|exists:t_Items,Id',
        'items.*.approved_qty' => 'required|numeric|min:1',
        'items.*.dispatched_qty' => 'required|integer|min:0', 
        'items.*.remarks' => 'nullable|string|max:255',
    ];
}
}