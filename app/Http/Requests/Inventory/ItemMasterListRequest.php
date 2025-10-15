<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemMasterListRequest extends FormRequest
{
    public function authorize()
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
            'BarCode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('t_Items', 'BarCode')->ignore($this->route('id')),
            ],
            'ItemName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('t_Items', 'ItemName')->ignore($this->route('id')),
            ],
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
            'remove_image' => 'nullable|in:1',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'BarCode.unique' => 'This barcode already exists in the system.',
            'ItemName.unique' => 'This item name already exists in the system.',
        ];
    }
}