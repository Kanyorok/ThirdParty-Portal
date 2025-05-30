<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThirdParies\Supplier;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SupplierListSeeder extends Seeder
{
    public function run()
    {
        // Optional: Clear existing records
        Supplier::query()->delete();
        DB::statement("DBCC CHECKIDENT ('t_Suppliers', RESEED, 0)");

        $now = Carbon::now();
        $createdBy = 1;

        // Get subcategories (non-null ParentId)
        $categories = ItemCategories::whereNotNull('ParentId')->get();

        $names = [
            'Global Tech Supplies', 'OfficeMart Ltd.', 'ProStationery Co.', 'Elite Business Solutions',
            'SmartOffice Hub', 'NextGen Supplies', 'Bureau World', 'PrimeTech Distributors',
            'Metro Office Essentials', 'Quick Supply Chain', 'Future Office Depot', 'Smart & Simple Supplies',
            'ExcelTech Vendors', 'Reliable Office Provisions', 'Efficient Supplies Inc.', 'Citywide Distributors',
            'FastTrack Stationery', 'EssentialBiz Ltd.', 'Nova Supply Partners', 'SharpEdge Office'
        ];

        $domains = ['example.com', 'suppliermail.com', 'officesupply.org', 'vendorhub.net'];

        for ($i = 0; $i < 20; $i++) {
            $name = $names[$i];
            $email = strtolower(str_replace(' ', '', $name)) . '@' . $domains[array_rand($domains)];
            $phone = '+2547' . rand(10000000, 99999999);
            $address = 'P.O. Box ' . rand(100, 999) . ', Nairobi, Kenya';
            $category = $categories->random();

            Supplier::create([
                'SupplierName' => $name,
                'ContactEmail' => $email,
                'ContactPhone' => $phone,
                'Address' => $address,
                'IsPrequalified' => rand(0, 1),
                'CategoryId' => $category->Id,
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ]);
        }
    }
}
