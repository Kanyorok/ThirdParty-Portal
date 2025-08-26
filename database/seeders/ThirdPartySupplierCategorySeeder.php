<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\SupplierCategory;

class ThirdPartySupplierCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = ThirdParties::suppliers()->take(5)->get();

        $categories = SupplierCategory::all();

        foreach ($suppliers as $supplier) {
            $randomCategories = $categories->random(rand(1, 3))->pluck('SupplierCategoryID');
            $supplier->supplierCategories()->sync($randomCategories);
        }
    }
}
