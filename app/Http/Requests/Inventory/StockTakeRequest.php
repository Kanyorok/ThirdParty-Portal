<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockTakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // StockTake header
            'BranchId' => 'required|exists:t_Branches,Id',
            'StoreId' => 'required|exists:t_Stores,Id',
            'CountedBy'     => 'required|string|max:100',
            'CountDate'     => 'required|date',

            // StockTake lines (assuming an array of lines)
            'lines'                         => 'required|array|min:1',
            'lines.*.ItemId'                => 'required|exists:t_StockItems,Id',
            'lines.*.ActualQuantity' => 'required|numeric|min:0',
            'lines.*.CountedQuantity' => 'required|numeric|min:0',
            'lines.*.Remarks'              => 'nullable|string|max:100',
        ];
    }
}