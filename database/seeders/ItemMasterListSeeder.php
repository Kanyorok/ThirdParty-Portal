<?php

namespace Database\Seeders;

use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ItemMasterListSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $createdBy = 1;

        $activeStatusId = DB::table('t_CodeDetails')
           ->where('CodeID', 'ItemStatus')
           ->where('Description', 'Active')
           ->value('ID');

        if (! $activeStatusId) {
            throw new \Exception("Active status not found in t_CodeDetails. Please seed it first.");
        }

        $itemTypeId = DB::table('t_CodeDetails')
        ->where('CodeID', 'ItemTypeStatus')
        ->where('Description', 'Stock')
        ->value('ID');

        $inventoryTypeId = DB::table('t_CodeDetails')
            ->where('CodeID', 'InventoryTypeStatus')
            ->where('Description', 'Durable')
            ->value('ID');

        // Preload categories and UOMs
        $categories = ItemCategories::with('parent')->whereNotNull('ParentId')->get();
        $uoms = DB::table('t_UOM')->pluck('Id', 'Code'); // ['PCS' => 1, ...]

        $items = [
            ['HP Laser Printer', 'PCS', 'Electronics > Printers'],
            ['Canon Ink Cartridge', 'PCS', 'Electronics > Printers'],
            ['Office Desk', 'PCS', 'Furniture > Desks'],
            ['Ergonomic Chair', 'PCS', 'Furniture > Chairs'],
            ['Wooden Filing Cabinet', 'PCS', 'Furniture > Cabinets'],
            ['A4 Copier Paper', 'REAM', 'Office Supplies > Paper Products'],
            ['Ballpoint Pens', 'BOX', 'Office Supplies > Pens & Pencils'],
            ['Stapler', 'PCS', 'Office Supplies > Binders & Folders'],
            ['Laptop Backpack', 'PCS', 'Electronics > Laptops'],
            ['Dell Latitude Laptop', 'PCS', 'Electronics > Laptops'],
            ['24-inch Monitor', 'PCS', 'Electronics > Monitors'],
            ['Executive Binder', 'PCS', 'Office Supplies > Binders & Folders'],
            ['Writing Pad', 'PCS', 'Office Supplies > Paper Products'],
            ['Wireless Mouse', 'PCS', 'Electronics > Monitors'],
            ['USB-C Cable', 'PCS', 'Electronics > Laptops'],
            ['Adjustable Chair Armrest', 'PCS', 'Furniture > Chairs'],
            ['Printer Stand', 'PCS', 'Furniture > Cabinets'],
            ['Correction Pen', 'BOX', 'Office Supplies > Pens & Pencils'],
            ['Plastic Folder', 'PACK', 'Office Supplies > Pens & Pencils'],
            ['Desk Organizer', 'PCS', 'Furniture > Desks'],
            ['Cable Management Sleeve', 'PCS', 'Electronics > Monitors'],
            ['Wireless Keyboard', 'PCS', 'Electronics > Laptops'],
            ['HDMI Cable', 'PCS', 'Electronics > Monitors'],
            ['Document Shredder', 'PCS', 'Office Supplies > Binders & Folders'],
            ['Whiteboard Markers', 'BOX', 'Office Supplies > Pens & Pencils'],
            ['Mouse Pad', 'PCS', 'Electronics > Monitors'],
            ['Laptop Stand', 'PCS', 'Electronics > Laptops'],
            ['Portable External Hard Drive', 'PCS', 'Electronics > Laptops'],
            ['Network Switch', 'PCS', 'Electronics > Networking'],
            ['Wireless Router', 'PCS', 'Electronics > Networking'],
            ['HD Webcam', 'PCS', 'Electronics > Monitors'],
            ['Bluetooth Speaker', 'PCS', 'Electronics > Audio'],
            ['Smartphone Stand', 'PCS', 'Electronics > Laptops'],
            ['Document Scanner', 'PCS', 'Office Supplies > Binders & Folders'],
            ['Cable Ties', 'PACK', 'Office Supplies > Miscellaneous'],
            ['Monitor Riser', 'PCS', 'Furniture > Desks'],
        ];

        $barcodeCounter = 1;

        foreach ($items as [$name, $uomCode, $categoryPath]) {
            [$parentName, $childName] = explode(' > ', $categoryPath);

            $category = $categories->first(function ($cat) use ($childName, $parentName) {
                return $cat->Name === $childName && optional($cat->parent)->Name === $parentName;
            });

            $uomId = $uoms[$uomCode] ?? null;

            if (! $category || ! $uomId) {
                echo "Skipping item '$name': category or UOM not found.\n";

                continue;
            }

            $barCode = 'BAR-' . str_pad($barcodeCounter++, 4, '0', STR_PAD_LEFT);

            // Create item first to get the ID
            $item = ItemMasterList::create([
                'ItemName' => $name,
                'UOM' => $uomId,
                'ItemType' => $itemTypeId,
                'InventoryType' => $inventoryTypeId,
                'Category' => $category->Id,
                'ItemDescription' => "$name for office use",
                'BarCode' => $barCode,
                'Status' => $activeStatusId,
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ]);

            // Then update the ItemCode based on the newly created ID
            $item->update([
                'ItemCode' => 'ITM-' . str_pad($item->Id, 5, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
