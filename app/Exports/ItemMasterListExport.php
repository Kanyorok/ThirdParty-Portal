<?php

namespace App\Exports;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\InventoryType;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemMasterListExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        $items = ItemMasterList::with([
            'itemType',
            'uom',
            'inventoryType',
            'category.parent'
        ])->get();

        $data = [];

        // ---- Item Rows ----
        foreach ($items as $item) {
            $category = $item->category;
            $parent = $category?->parent;

            // Determine whether the assigned category is a parent or child
            $categoryName = $parent ? $category->Name : ($category?->Name ?? '-');
            $parentName = $parent?->Name ?? ($category && !$parent ? $category->Name : '-');

            $data[] = [
                $item->ItemCode ?? '-',
                $item->BarCode ?? '-',
                $item->ItemName ?? '-',
                $item->itemType?->TypeName ?? '-',
                $item->uom?->Code ?? '-',
                $item->inventoryType?->Type ?? '-',
                $categoryName,
                $parentName,
            ];
        }

        // ---- Spacer ----
        $data[] = [];
        $data[] = ['--- Reference: Available Options for Dropdown Fields ---'];
        $data[] = [];

        // ---- Item Types ----
        $data[] = ['--- Item Types ---'];
        foreach (ItemType::all() as $type) {
            $data[] = [$type->TypeName];
        }

        // ---- Spacer ----
        $data[] = [];
        $data[] = ['--- Units of Measure (UOM) ---'];
        foreach (UnitOfMeasure::all() as $uom) {
            $data[] = [$uom->Code];
        }

        // ---- Spacer ----
        $data[] = [];
        $data[] = ['--- Inventory Types ---'];
        foreach (InventoryType::all() as $invType) {
            $data[] = [$invType->Type];
        }

        // ---- Spacer ----
        $data[] = [];
        $data[] = ['--- Categories & Subcategories ---'];
        foreach (ItemCategories::with('parent')->get() as $cat) {
            $data[] = [
                'Category' => $cat->Name,
                'Parent Category' => $cat->parent?->Name ?? '-',
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'ItemCode',
            'BarCode',
            'ItemName',
            'ItemType',
            'UOM',
            'InventoryType',
            'Category',
            'ParentCategory',
        ];
    }
}
