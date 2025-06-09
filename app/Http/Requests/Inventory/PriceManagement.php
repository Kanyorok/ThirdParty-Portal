<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class PriceManagement extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'PriceID' => 'required|string|max:255',
            'ItemID' => 'required|exists:t_Items,Id',
            'UOM' => 'required|exists:t_Items,Id', 
            'SKUCode' => 'required|exists:t_StockItems,Id',
            'EstimatedPrice' => 'required|numeric|min:0',
            'ActualPrice' => 'required|numeric|min:0',
            'CurrencyCode' => 'required|string|max:10',
            'EffectiveFrom' => 'required|date',
            'EffectiveTo' => 'nullable|date|after_or_equal:EffectiveFrom',
            'IsDefault' => 'boolean',
            'Source' => 'nullable|string|max:255',
        ];
    }
}