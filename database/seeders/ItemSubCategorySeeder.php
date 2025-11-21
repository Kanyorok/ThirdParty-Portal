<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSubCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('t_ItemSubCategories')->insert([
            [
                'SubCategoryCode' => 'SUB-001',
                'SubCategoryName' => 'Electronics',
                'ParentCategory'  => 16,
                'Description'     => 'Subcategory for electronic items',
                'Status'          => true,
            ],
            [
                'SubCategoryCode' => 'SUB-002',
                'SubCategoryName' => 'Furniture',
                'ParentCategory'  => 17,
                'Description'     => 'Furniture related items',
                'Status'          => true,
            ],
        ]);
    }
}