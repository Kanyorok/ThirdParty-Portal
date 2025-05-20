<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventory\ItemCategories;

class ItemCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
        ItemCategories::insert([
[
    [
        'CategoryCode'   => 'ELEC001',
        'Name'          => 'Electronics',
        'Description'   => 'Devices and gadgets',
        'Status'        => true,
        'CreatedBy'     => 34, // ID of the user who created it
        'ModifiedBy'    => 02, // ID of the user who last modified it
        'DeletedBy'     => null, // NULL if not deleted
    ],
    [
        'CategoryCode'   => 'HOME002',
        'Name'          => 'Home Appliances',
        'Description'   => 'Household electrical items',
        'Status'        => true,
        'CreatedBy'     => 02,
        'ModifiedBy'    => 02,
        'DeletedBy'     => null,
    ]
]

        ]);
    }
}
