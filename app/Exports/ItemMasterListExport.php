<?php

namespace App\Exports;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\UnitOfMeasure;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ItemMasterListExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Items' => new ItemsSheet(),
            'ReferenceData' => new ReferenceDataSheet(),
        ];
    }
}

class ItemsSheet implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        $items = ItemMasterList::with([
            'itemType', // Relationship to CodeDetails for ItemType
            'uom',
            'inventoryType', // Relationship to CodeDetails for InventoryType
            'status', // Relationship to CodeDetails for Status
            'price',
            'category.parent'
        ])->get();

        $data = [];

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
                $item->itemType?->Description ?? '-', 
                $item->uom?->Code ?? '-',
                $item->inventoryType?->Description ?? '-', 
                $categoryName,
                $parentName,
                $item->price?->ActualPrice ?? '-', 
                $item->status?->Description ?? '-', 
                $item->ItemDescription ?? '-',
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
            'ItemPrice',
            'Status',
            'ItemDescription',
        ];
    }

    public function title(): string
    {
        return 'Items';
    }
}

class ReferenceDataSheet implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        $data = [];
        
        // Header
        $data[] = ['--- Available Options for Reference ---'];
        $data[] = ['(This sheet is for reference only - do not import this data)'];
        $data[] = [];

        // Item Types from CodeDetails
        $data[] = ['Item Types:'];
        foreach (CodeDetail::where('CodeID', 'ItemTypeStatus')->get() as $type) {
            $data[] = ['- ' . $type->Description];
        }
        $data[] = [];

        // UOMs
        $data[] = ['Units of Measure (UOM):'];
        foreach (UnitOfMeasure::all() as $uom) {
            $data[] = ['- ' . $uom->Code];
        }
        $data[] = [];

        // Inventory Types from CodeDetails
        $data[] = ['Inventory Types:'];
        foreach (CodeDetail::where('CodeID', 'InventoryTypeStatus')->get() as $invType) {
            $data[] = ['- ' . $invType->Description];
        }
        $data[] = [];

        // Statuses from CodeDetails
        $data[] = ['Item Statuses:'];
        foreach (CodeDetail::where('CodeID', 'ItemStatus')->get() as $status) {
            $data[] = ['- ' . $status->Description];
        }
        $data[] = [];

        // Categories
        $data[] = ['Categories & Subcategories:'];
        foreach (ItemCategories::with('parent')->get() as $cat) {
            $parentName = $cat->parent?->Name ?? '(Main Category)';
            $data[] = ['- ' . $cat->Name . ' → Parent: ' . $parentName];
        }

        return $data;
    }

    public function headings(): array
    {
        return ['Reference Data'];
    }

    public function title(): string
    {
        return 'ReferenceData';
    }
}