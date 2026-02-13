<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class TransactionTransferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function prepareForValidation()
    {
        $items = $this->get('items', []);
        if (is_array($items)) {
            foreach ($items as $key => $item) {
                if (isset($item['batch_allocation']) && is_string($item['batch_allocation'])) {
                    if (!empty($item['batch_allocation'])) {
                        try {
                            $decoded = json_decode($item['batch_allocation'], true);
                            if (is_array($decoded)) {
                                $items[$key]['batch_allocation'] = $decoded;
                            } else {
                                $items[$key]['batch_allocation'] = null;
                            }
                        } catch (\Exception $e) {
                            $items[$key]['batch_allocation'] = null;
                        }
                    } else {
                        $items[$key]['batch_allocation'] = null;
                    }
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    public function rules()
    {
        $rules = [
            'RequisitionType' => 'required|string|in:interbranch,procurement',
            'TransferDate'    => 'required|date',
            'TransferredBy'   => 'required|string',
            'FromBranch'      => 'nullable|exists:t_Branches,Id',
            'ToBranch'        => 'required|exists:t_Branches,Id',

            'items'                         => 'required|array|min:1',
            'items.*.item'                 => 'required|exists:t_Items,Id',
            'items.*.unit_cost'            => 'required|numeric|min:0',
            'items.*.uom'                 => 'required|exists:t_UOM,Id',
            'items.*.approved_qty'        => 'required|numeric|min:1',
            'items.*.dispatched_qty'      => 'required|numeric|min:0',
            'items.*.remarks'             => 'nullable|string|max:255',
            'items.*.batch_allocation'    => 'nullable|array',
            'items.*.batch_allocation.*.ledger_id' => 'required_with:items.*.batch_allocation|exists:t_StockGRNLedger,Id',
            'items.*.batch_allocation.*.quantity'  => 'required_with:items.*.batch_allocation|numeric|min:0',
        ];

        // ✅ FIXED: procurement requisitions come from t_Requisitions, not t_GoodsReceipts
        if ($this->input('RequisitionType') === 'procurement') {
            $rules['RequisitionId'] = 'required|exists:t_Requisitions,Id';
        } else {
            $rules['RequisitionId'] = 'required|exists:t_InterBranchRequisition,Id';
        }

        return $rules;
    }
}