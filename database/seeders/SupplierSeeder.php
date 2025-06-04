<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThirdParies\Supplier;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $createdBy = 1;

        $categories = ItemCategories::all();

        $suppliersData = [];

        // Ensure at least 2 suppliers per category
        foreach ($categories as $category) {
            for ($i = 0; $i < 2; $i++) {
                $suppliersData[] = [
                    'SupplierName' => $this->generateSupplierName($category->Name),
                    'ContactEmail' => Str::slug($category->Name) . "+$i@" . 'example.com',
                    'ContactPhone' => '07' . rand(10000000, 99999999),
                    'Address' => 'P.O. Box ' . rand(100, 999) . ', City Center',
                    'IsPrequalified' => (bool)rand(0, 1),
                    'CategoryId' => $category->Id,
                    'CreatedBy' => $createdBy,
                    'ModifiedBy' => $createdBy,
                    'CreatedOn' => $now,
                    'ModifiedOn' => $now,
                ];
            }
        }

        // Add extra suppliers to reach 20+
        while (count($suppliersData) < 24) {
            $category = $categories->random();
            $suppliersData[] = [
                'SupplierName' => $this->generateSupplierName($category->Name),
                'ContactEmail' => Str::slug($category->Name) . "+x" . rand(100, 999) . '@example.com',
                'ContactPhone' => '07' . rand(10000000, 99999999),
                'Address' => 'Industrial Area, Plot ' . rand(1, 100),
                'IsPrequalified' => (bool)rand(0, 1),
                'CategoryId' => $category->Id,
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ];
        }

        foreach ($suppliersData as $supplier) {
            Supplier::create($supplier);
        }
    }

    private function generateSupplierName($categoryName)
    {
        $prefixes = ['Global', 'Mega', 'Prime', 'Nova', 'Elite', 'Capital', 'Smart', 'Alpha'];
        $suffixes = ['Suppliers', 'Distributors', 'Solutions', 'Enterprises', 'Corp', 'Traders'];

        return $prefixes[array_rand($prefixes)] . ' ' .
               Str::title($categoryName) . ' ' .
               $suffixes[array_rand($suffixes)];
    }
}
