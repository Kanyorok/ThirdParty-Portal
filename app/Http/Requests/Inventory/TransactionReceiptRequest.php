<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransactionReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'TransferID' => 'required|exists:t_Transfers,Id',
            'ReceivedBy' => 'required|string',
            'ReceivedDate' => 'required|date',
            'GeneralRemarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|exists:t_Items,Id',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.uom' => 'required|exists:t_UOM,Id',
            'items.*.dispatched_qty' => 'nullable|numeric|min:0',
            'items.*.discrepancy' => 'nullable|numeric',
            'items.*.received_qty' => 'required|numeric|min:0',
            'items.*.store_id' => 'nullable|exists:t_Stores,Id',
            'items.*.damaged_qty' => 'nullable|numeric|min:0',

        ];
    }
}
