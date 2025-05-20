<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuppliersSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('t_Suppliers')->insert([
            [
                'SupplierName' => 'TechTrend Solutions',
                'ContactEmail' => 'contact@techtrend.com',
                'ContactPhone' => '+254-700-123-456',
                'Address' => '123 Innovation Park, Nairobi, Kenya',
                'IsPrequalified' => true,
                'CategoryId' => 1, // Assumes category ID 1 exists in t_ItemCategories
                'CreatedBy' => 1, // Assumes user ID 1 exists in t_Users
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'SupplierName' => 'Global Supplies Ltd',
                'ContactEmail' => 'info@globalsupplies.co.ke',
                'ContactPhone' => '+254-722-987-654',
                'Address' => '456 Enterprise Road, Mombasa, Kenya',
                'IsPrequalified' => false,
                'CategoryId' => 2, // Assumes category ID 2 exists in t_ItemCategories
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'SupplierName' => 'EcoFriendly Vendors',
                'ContactEmail' => 'sales@ecofriendlyvendors.com',
                'ContactPhone' => '+254-733-555-789',
                'Address' => '789 Green Lane, Kisumu, Kenya',
                'IsPrequalified' => true,
                'CategoryId' => 1, // Assumes category ID 1 exists in t_ItemCategories
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
