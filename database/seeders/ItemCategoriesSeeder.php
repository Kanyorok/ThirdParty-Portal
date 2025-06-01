<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ItemCategoriesSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $createdBy = 1;

        $categories = [
            'Office Supplies' => [
                'Pens & Pencils',
                'Paper Products',
                'Binders & Folders',
                'Miscellaneous', 
            ],
            'Electronics' => [
                'Laptops',
                'Printers',
                'Monitors',
                'Networking', 
                'Audio',      
            ],
            'Furniture' => [
                'Desks',
                'Chairs',
                'Cabinets',
            ],
        ];

        foreach ($categories as $parentName => $subCategories) {
            $parent = ItemCategories::firstOrCreate(
                ['Name' => $parentName],
                [
                    'Description' => "$parentName for company use",
                    'CreatedBy' => $createdBy,
                    'ModifiedBy' => $createdBy,
                    'CreatedOn' => $now,
                    'ModifiedOn' => $now,
                    'CategoryCode' => strtoupper(substr($parentName, 0, 3)) . '-PARENT',
                ]
            );

            foreach ($subCategories as $childName) {
                ItemCategories::firstOrCreate(
                    ['Name' => $childName, 'ParentId' => $parent->Id],
                    [
                        'Description' => "$childName under $parentName",
                        'CreatedBy' => $createdBy,
                        'ModifiedBy' => $createdBy,
                        'CreatedOn' => $now,
                        'ModifiedOn' => $now,
                        'CategoryCode' => strtoupper(substr($childName, 0, 3)) . '-' . rand(100, 999),
                    ]
                );
            }
        }
    }
}
