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
    // Disable constraints
    DB::statement('ALTER TABLE t_ItemCategories NOCHECK CONSTRAINT ALL');

    // Delete all records
    ItemCategories::query()->forceDelete();

    // Reseed identity
    DB::statement("DBCC CHECKIDENT ('t_ItemCategories', RESEED, 0)");

    // Enable constraints again
    DB::statement('ALTER TABLE t_ItemCategories WITH CHECK CHECK CONSTRAINT ALL');

    // Seed data
    $now = \Illuminate\Support\Carbon::now();
    $createdBy = 1;

    $categories = [
        'Office Supplies' => ['Pens & Pencils', 'Paper Products', 'Binders & Folders'],
        'Electronics'     => ['Laptops', 'Printers', 'Monitors'],
        'Furniture'       => ['Desks', 'Chairs', 'Cabinets'],
    ];

    foreach ($categories as $parentName => $subCategories) {
        $parent = ItemCategories::create([
            'Name'        => $parentName,
            'Description' => "$parentName for company use",
            'CreatedBy'   => $createdBy,
            'ModifiedBy'  => $createdBy,
            'CreatedOn'   => $now,
            'ModifiedOn'  => $now,
        ]);

        foreach ($subCategories as $childName) {
            ItemCategories::create([
                'Name'        => $childName,
                'Description' => "$childName under $parentName",
                'ParentId'    => $parent->Id,
                'CreatedBy'   => $createdBy,
                'ModifiedBy'  => $createdBy,
                'CreatedOn'   => $now,
                'ModifiedOn'  => $now,
            ]);
        }
    }
}

}
