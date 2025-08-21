<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Facades\DB;
use App\Models\Core\CodeDetail;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User;
use Illuminate\Support\Carbon;

class ItemCategoriesSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $createdBy = 1;

        // Get the Active status ID from t_CodeDetails
        $activeStatusId = DB::table('t_CodeDetails')
            ->where('CodeID', 'CategoryStatus')
            ->where('Description', 'Active')
            ->value('ID');

        if (!$activeStatusId) {
            throw new \Exception("Active status not found in t_CodeDetails. Please seed it first.");
        }

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
            // Create or get parent category
            $parent = ItemCategories::firstOrCreate(
                ['Name' => $parentName],
                [
                    'Description' => "$parentName for company use",
                    'CreatedBy' => $createdBy,
                    'ModifiedBy' => $createdBy,
                    'CreatedOn' => $now,
                    'ModifiedOn' => $now,
                    'CategoryCode' => strtoupper(substr($parentName, 0, 3)) . '-PARENT',
                    'Status' => $activeStatusId, 
                ]
            );

            // Create subcategories
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
                        'Status' => $activeStatusId, 
                    ]
                );
            }
        }
    }
}
