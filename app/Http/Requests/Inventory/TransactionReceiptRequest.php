<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class TransactionReceiptRequest extends FormRequest
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
            'TransferID' => 'required|exists:t_Transfers,Id',
            'ReceivedBy' => 'required|string',
            'ReceivedDate' => 'required|date',
            'GeneralRemarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|exists:t_Items,Id',
            'items.*.dispatched_qty' => 'nullable|numeric|min:0',
            'items.*.discrepancy' => 'nullable|numeric',
            'items.*.received_qty' => 'required|numeric|min:0',
            'items.*.damaged_qty' => 'nullable|numeric|min:0',

        ];
    }

}
