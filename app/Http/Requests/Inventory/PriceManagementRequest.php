<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class PriceManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'ActualPrice' => 'required|numeric|min:0',
            'CurrencyCode' => 'required|string|max:10',
            'EffectiveFrom' => 'required|date',
            'EffectiveTo' => 'nullable|date|after_or_equal:EffectiveFrom',
            'IsDefault' => 'boolean',
            'Source' => 'nullable|string|max:255',
        ];

        if ($this->isMethod('post')) {
            $rules['ItemID'] = 'required|exists:t_Items,Id';
            $rules['UOM'] = 'required|exists:t_UOM,Id';
        }

        return $rules;
    }
}
