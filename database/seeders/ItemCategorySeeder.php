<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Procurement\ItemCategory;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed some example categories
        ItemCategory::insert([
            ['name' => 'Electronics'],
            ['name' => 'Furniture'],
            ['name' => 'Stationery'],
            ['name' => 'Services'],
        ]);
    }
}
