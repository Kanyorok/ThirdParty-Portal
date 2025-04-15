<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Procurement\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::create([
                      'name'            => 'Sample Item',
                      'type'            => 'good',
                      'description'     => 'This is a sample item.',
                      'category_id'     => 1, // Ensure this category exists in the database
                      'unit_of_measure' => 'pcs',
                      'unit_price'      => 100.00,
                      'service_scope'   => null,
                     ]);
    }
}
