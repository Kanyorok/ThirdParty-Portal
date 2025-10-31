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
        // If it's the upload route, only validate file
        if ($this->routeIs('pricemanagement.upload')) {
            return [
                'file' => 'required|file|mimes:xlsx,csv,xls|max:2048',
            ];
        }

        // Otherwise (create/update price)
        $rules = [
            'ActualPrice' => 'required|numeric|min:0',
            'CurrencyCode' => 'required|string|max:10',
            'EffectiveFrom' => 'nullable|date',
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
