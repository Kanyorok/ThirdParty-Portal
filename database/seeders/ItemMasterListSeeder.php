<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventory\ItemMasterList;

class ItemMasterListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
        ItemMasterList::insert([
            [
                'ItemCode'      => '001',
                'BarCode'       => 'ABC123',
                'ItemName'      => 'Laptop',
                'ItemType'      => 'Electronics',
                'Category'      => 'Computers',
                'SubCategory'   => 'Gaming Laptops',
                'UOM'           => 'Piece',
                'InventoryType' => 'Stock',
            ],
            [
                'ItemCode'       => '0007',
                'BarCode'      => 'XYZ456',
                'ItemName'      => 'Smartphone',
                'ItemType'      => 'Electronics',
                'Category'      => 'Mobile Devices',
                'SubCategory'   => 'Flagship Phones',
                'UOM'           => 'Piece',
                'InventoryType' => 'Stock',
            ]
        ]);
    }
}
