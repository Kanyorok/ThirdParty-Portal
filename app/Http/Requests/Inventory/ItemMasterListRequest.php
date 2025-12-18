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
        // Your route uses "Id" as the parameter
        $itemId = $this->route('Id') ?? $this->route('id');

        $rules = [
            'ItemType' => 'required|exists:t_CodeDetails,ID',
            'Category' => 'required|exists:t_ItemCategories,Id',
            'SubCategory' => 'nullable|exists:t_ItemCategories,Id',
            'UOM' => 'required|exists:t_UOM,Id',
            'InventoryType' => 'required|exists:t_CodeDetails,ID',
            'ImageUpload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'Document' => 'nullable|file|max:2048',
            'Document.*' => 'nullable|file|max:2048',
            'ItemDescription' => 'required|string',
            'Status' => 'nullable|exists:t_CodeDetails,ID',
            'ItemPrice' => 'nullable|string',
            'remove_image' => 'nullable|boolean',
        ];

        // If no item ID present => Creating mode
        if (!$itemId) {
            $rules['BarCode'] = [
                'nullable',
                'regex:/^[A-Za-z0-9]+$/',
                'max:255',
                Rule::unique('t_Items', 'BarCode')->whereNull('DeletedOn'), // <- exclude soft-deleted
            ];

            $rules['ItemName'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('t_Items', 'ItemName')->whereNull('DeletedOn'), // <- exclude soft-deleted
            ];
        }
        else {
            // Update mode (ignore the current record)
            $rules['BarCode'] = [
                'nullable',
                'regex:/^[A-Za-z0-9]+$/',
                'max:255',
                Rule::unique('t_Items', 'BarCode')->ignore($itemId, 'Id')->whereNull('DeletedOn'), // <- exclude soft-deleted
            ];

            $rules['ItemName'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('t_Items', 'ItemName')->ignore($itemId, 'Id')->whereNull('DeletedOn'), // <- exclude soft-deleted
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
