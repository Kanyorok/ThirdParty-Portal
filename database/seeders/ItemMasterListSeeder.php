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
                'ItemCode'      => '003',
                'BarCode'       => 'BSI123',
                'ItemName'      => 'Lenovo Thinkpad',
                'ItemType'      => 'Electronics',
                'Category'      => 'Computers',
                'SubCategory'   => 'Gaming Laptops',
                'UOM'           => 'Piece',
                'InventoryType' => 'Stock',
            ],
            [
                'ItemCode'       => '0004',
                'BarCode'      => 'RCBS456',
                'ItemName'      => 'Smartphone',
                'ItemType'      => 'Electronics',
                'Category'      => 'Mobile Devices',
                'SubCategory'   => 'Phones',
                'UOM'           => 'Piece',
                'InventoryType' => 'Stock',
            ]
        ]);
    }
}
