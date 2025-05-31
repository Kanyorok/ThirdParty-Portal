<?php

namespace App\Http\Requests\Inventory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemMasterListRequest extends FormRequest
{
    public function authorize()
    {
        // Adjust based on action
        if ($this->isMethod('post')) {
            return $this->user()->can('create', \App\Models\Inventory\ItemMasterList::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $item = $this->route('itemmasterlist'); 
            return $item ? $this->user()->can('update', $item) : false;
        }

        return false;
    }
     /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules()
    {
        return [
            'BarCode' => 'required|string|max:255',
            'ItemName' => 'required|string|max:255',
            'ItemType' => 'required|exists:t_ItemTypes,Id',
            'Category' => 'required|exists:t_ItemCategories,Id',
            'SubCategory' => 'nullable|exists:t_ItemCategories,Id',
            'UOM' => 'required|exists:t_UOM,Id',
            'InventoryType' => 'required|exists:t_InventoryTypes,Id',
            'ImageUpload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'DocumentUpload' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
            'ItemDescription' => 'nullable|string',
            'remove_image' => 'nullable|in:1',
        ];
    }
}
