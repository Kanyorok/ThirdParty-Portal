<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('t_ItemCategories')->insert([
            [
                'Name' => 'Electronics',
                'Description' => 'Devices and gadgets powered by electricity.',
                'ParentId' => null,
                'CreatedBy' => 1, // Assumes user ID 1 exists in t_Users
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'Name' => 'Computers',
                'Description' => 'Desktops, laptops, and related peripherals.',
                'ParentId' => 1, // References 'Electronics' category (Id = 1)
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'Name' => 'Office Supplies',
                'Description' => 'Stationery and office equipment.',
                'ParentId' => null,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'Name' => 'Furniture',
                'Description' => 'Office desks, chairs, and storage units.',
                'ParentId' => 3, // References 'Office Supplies' category (Id = 3)
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
        ]);
    }
}
