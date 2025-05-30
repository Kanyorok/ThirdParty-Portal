<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ItemMasterListSeeder extends Seeder
{
    public function run()
    {
        // Optional: Clear table before seeding
        ItemMasterList::query()->delete();
        DB::statement("DBCC CHECKIDENT ('t_Items', RESEED, 0)");

        $now = Carbon::now();
        $createdBy = 1;

        // Get foreign key IDs for ItemType and InventoryType
        $itemTypeId = DB::table('t_ItemTypes')->where('TypeName', 'Stock')->value('Id');
        $inventoryTypeId = DB::table('t_InventoryTypes')->where('Type', 'Consumable')->value('Id');

        // Fetch subcategories (where ParentId is not null)
        $categories = ItemCategories::with('parent')->whereNotNull('ParentId')->get();

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
            ['Plastic Folder', 'PACK', 'Office Supplies > Binders & Folders'],
            ['Monitor Riser', 'PCS', 'Furniture > Desks'],
        ];

        $barcodeCounter = 1;

        foreach ($items as [$name, $uom, $categoryPath]) {
            [$parentName, $childName] = explode(' > ', $categoryPath);

            $category = $categories->first(function ($cat) use ($childName, $parentName) {
                return $cat->Name === $childName && $cat->parent && $cat->parent->Name === $parentName;
            });

            if (!$category) {
                continue; // Skip if matching category not found
            }

            $barCode = 'BAR-' . str_pad($barcodeCounter++, 4, '0', STR_PAD_LEFT);

            ItemMasterList::create([
                'ItemName' => $name,
                'UOM' => $uom,
                'ItemType' => $itemTypeId,
                'InventoryType' => $inventoryTypeId,
                'Category' => $category->Id,
                'ItemDescription' => $name . ' for office use',
                'BarCode' => $barCode,
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ]);
        }
    }
}
