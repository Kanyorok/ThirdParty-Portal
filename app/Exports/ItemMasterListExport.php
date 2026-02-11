<?php

namespace App\Exports;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\UnitOfMeasure;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemMasterListExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        // Load items with all relationships
        $items = ItemMasterList::with([
            'itemType.type',      // ItemType relationship through t_ItemTypes
            'uom',
            'inventoryType.type', // InventoryType relationship through t_InventoryTypes
            'status',
            'price',
            'category.parent',
        ])->get();

        $data = [];

        foreach ($items as $item) {
            $category = $item->category;
            $parent = $category?->parent;

            // Determine whether the assigned category is a parent or child
            $categoryName = $parent ? $category->Name : ($category?->Name ?? '');
            $parentName = $parent?->Name ?? ($category && ! $parent ? $category->Name : '');

            $data[] = [
                $item->ItemCode ?? '',
                $item->BarCode ?? '',
                $item->ItemName ?? '',
                $item->itemType?->type?->Description ?? '', // Get Description from CodeDetail through ItemType
                $item->uom?->Code ?? '',
                $item->inventoryType?->type?->Description ?? '', // Get Description from CodeDetail through InventoryType
                $categoryName,
                $parentName,
                $item->price?->ActualPrice ?? '',
                $item->status?->Description ?? '',
                $item->ItemDescription ?? '',
            ];
        }

        // Add spacing and reference data
        $data[] = [];
        $data[] = ['--- REFERENCE DATA (Do not modify this section) ---'];
        $data[] = [];

        // Item Types from t_ItemTypes
        $data[] = ['--- Available Item Types ---'];
        $itemTypes = ItemType::with('type')
            ->where('Active', 1)
            ->get()
            ->sortBy('type.Description');
        foreach ($itemTypes as $type) {
            if ($type->type) {
                $data[] = ['ItemType: ' . $type->type->Description];
            }
        }
        $data[] = [];

        // UOMs
        $data[] = ['--- Available Units of Measure (UOM) ---'];
        foreach (UnitOfMeasure::where('Active', 1)->orderBy('Code')->get() as $uom) {
            $data[] = ['UOM: ' . $uom->Code];
        }
        $data[] = [];

        // Inventory Types from t_InventoryTypes
        $data[] = ['--- Available Inventory Types ---'];
        $inventoryTypes = InventoryType::with('type')
            ->where('Status', 1)
            ->get()
            ->sortBy('type.Description');
        foreach ($inventoryTypes as $invType) {
            if ($invType->type) {
                $data[] = ['InventoryType: ' . $invType->type->Description];
            }
        }
        $data[] = [];

        // Statuses from CodeDetails
        $data[] = ['--- Available Item Statuses ---'];
        foreach (CodeDetail::where('CodeID', 'ItemStatus')->orderBy('Description')->get() as $status) {
            $data[] = ['Status: ' . $status->Description];
        }
        $data[] = [];

        // Categories with hierarchy
        $data[] = ['--- Available Categories & Subcategories ---'];
        $categories = ItemCategories::with('parent')
            ->whereHas('status', fn ($q) => $q->where('Description', 'Active'))
            ->orderBy('Name')
            ->get();

        foreach ($categories as $cat) {
            $parentName = $cat->parent?->Name ?? '(Main Category)';
            $data[] = [
                'Category: ' . $cat->Name,
                'Parent: ' . $parentName,
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
}
