<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Inventory\ItemMasterList;

class ItemMasterListRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $itemId = $this->route('id') ?? $this->route('Id'); // Support both cases
        $isUpdate = $itemId !== null;

        $rules = [
            'ItemType' => 'required|exists:t_ItemTypes,Id',
            'Category' => 'required|exists:t_ItemCategories,Id',
            'SubCategory' => 'nullable|exists:t_ItemCategories,Id',
            'UOM' => 'required|exists:t_UOM,Id',
            'InventoryType' => 'required|exists:t_InventoryTypes,Id',
            'ImageUpload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'DocumentUpload' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
            'ItemDescription' => 'nullable|string',
            'Status' => 'nullable|exists:t_CodeDetails,ID',
            'ItemPrice' => 'nullable|string',
            'remove_image' => 'nullable|boolean',

        ];

        // ✅ Apply unique rule only for creation
        if (!$isUpdate) {
            $rules['BarCode'] = 'required|string|max:255|unique:t_Items,BarCode';
            $rules['ItemName'] = 'required|string|max:255|unique:t_Items,ItemName';
        } else {
            // ✅ For updates — allow same value but check if changed
            $item = ItemMasterList::find($itemId);

            $rules['BarCode'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('t_Items', 'BarCode')->ignore($itemId),
            ];

            $rules['ItemName'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('t_Items', 'ItemName')->ignore($itemId),
            ];

            // Optional optimization — skip DB query if same value
            if ($item && $this->input('BarCode') === $item->BarCode) {
                unset($rules['BarCode']);
            }
            if ($item && $this->input('ItemName') === $item->ItemName) {
                unset($rules['ItemName']);
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'BarCode.unique' => 'This barcode already exists in the system.',
            'ItemName.unique' => 'This item name already exists in the system.',
        ];
    }
}
