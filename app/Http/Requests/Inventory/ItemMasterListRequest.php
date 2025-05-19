<?php

namespace App\Http\Requests\Inventory;

use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemSubCategories;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemMasterListRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'BarCode' => 'required',
            'ItemName' => 'required',
            'ItemType' => 'required',
            'Category' => 'required',
            'SubCategory' => 'required',
            'UOM' => 'required',
            'InventoryType' => 'required',
            'ImageUpload' => ['nullable', Rule::imageFile()->max('2mb')],
            'ItemDescription' => 'nullable',
            'DocumentUpload' => 'nullable',
        ];
    }

    public function getImage(): \Illuminate\Http\UploadedFile|null
    {
        if ($this->hasFile('ImageUpload')) {
            return $this->file('ImageUpload');
        }
        return null;
    }

    public function getCategory(): ItemCategories
    {
        $category = ItemCategories::query()->where('Id', $this->validated('Category'))->first();
        if ($category instanceof ItemCategories) {
            return $category;
        }
        throw ValidationException::withMessages([
            'Category' => 'Category Not Found',
        ]);
    }

    public function getSubCategory(): ItemSubCategories
    {
        $subcategory = ItemSubCategories::query()->where('Id', $this->validated('SubCategory'))->first();
        if ($subcategory instanceof ItemSubCategories) {
            return $subcategory;
        }
        throw ValidationException::withMessages([
            'SubCategory' => 'SubCategory Not Found',
        ]);
    }

    public function messages(): array
    {
        return [
            'ItemCode.unique' => '🚨 The ItemCode already exists! Please choose a different code.',
        ];
    }

    public function getItemCode():string
    {
        $number = ItemMasterList::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('ITEM' . Str::padLeft(($number), 4, '0'));
        } while (ItemMasterList::query()->where('ItemCode', $slug)->withTrashed()->exists());

        return $slug;
    }
}
