<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemMasterListRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $itemId = $this->route('Id'); // Use the parameter name from your route

        $rules = [
            'ItemType' => 'required|exists:t_ItemTypes,Id',
            'Category' => 'required|exists:t_ItemCategories,Id',
            'SubCategory' => 'nullable|exists:t_ItemCategories,Id',
            'UOM' => 'required|exists:t_UOM,Id',
            'InventoryType' => 'required|exists:t_InventoryTypes,Id',
            'ImageUpload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'Document' => 'nullable|file|max:2048',
            'Document.*' => 'nullable|file|max:2048',
            'ItemDescription' => 'nullable|string',
            'Status' => 'nullable|exists:t_CodeDetails,ID',
            'ItemPrice' => 'nullable|string',
            'remove_image' => 'nullable|boolean',
        ];

        // For creation
        if (!$itemId) {
            $rules['BarCode'] = [
                'nullable', // ✅ optional now
                'regex:/^[A-Za-z0-9]+$/', // ✅ only letters & numbers
                'max:255',
                'unique:t_Items,BarCode',
            ];
            $rules['ItemName'] = 'required|string|max:255|unique:t_Items,ItemName';
        } else {
            // For update
            $rules['BarCode'] = [
                'nullable',
                'regex:/^[A-Za-z0-9]+$/',
                'max:255',
                Rule::unique('t_Items', 'BarCode')->ignore($itemId),
            ];
            $rules['ItemName'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('t_Items', 'ItemName')->ignore($itemId),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'BarCode.unique' => 'This barcode already exists in the system.',
            'BarCode.regex' => 'The barcode may only contain letters and numbers (no spaces or symbols).',
            'ItemName.unique' => 'This item name already exists in the system.',
        ];
    }
}
