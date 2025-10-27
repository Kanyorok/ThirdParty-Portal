<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierCategoryItemCategorySeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🔗 Seeding Supplier-Category to Item-Category relationships...');

        // Check if relationships already exist
        $existingCount = DB::table('t_SupplierCategory_ItemCategory')
            ->whereNull('DeletedOn')
            ->count();

        if ($existingCount > 0) {
            $this->command->info("   Found {$existingCount} existing relationships.");
        }

        // Get available supplier categories
        $supplierCategories = DB::table('t_SupplierCategories')
            ->where('IsActive', 1)
            ->get();

        // Get available main item categories (Status 290 = Active, ParentId is null)
        $itemCategories = DB::table('t_ItemCategories')
            ->where('Status', 290)
            ->whereNull('ParentId') // Only main categories
            ->get();

        $this->command->info("   Found {$supplierCategories->count()} supplier categories and {$itemCategories->count()} item categories");

        if ($supplierCategories->isEmpty() || $itemCategories->isEmpty()) {
            $this->command->warn('   ⚠️  Need both supplier categories and item categories to create relationships');
            return;
        }

        // Create relationships based on existing main categories and create supplier categories as needed
        $relationships = [
            // Electronics Supplier Category -> Electronics main category
            [
                'supplier_category_name' => 'Electronics & IT Equipment',
                'item_categories' => ['Electronics'] // This exists in the database
            ],
            // Office Supplies Supplier Category -> Office Supplies main category
            [
                'supplier_category_name' => 'Office Supplies & Stationery',
                'item_categories' => ['Office Supplies'] // This exists in the database
            ],
        ];

        // Add any other existing main categories to a general supplier category
        $generalSupplierCategory = 'General Supplies';
        $otherMainCategories = $itemCategories->whereNotIn('Name', ['Electronics', 'Office Supplies'])->pluck('Name')->toArray();

        if (!empty($otherMainCategories)) {
            $relationships[] = [
                'supplier_category_name' => $generalSupplierCategory,
                'item_categories' => $otherMainCategories
            ];
        }

        $created = 0;
        $now = now();

        foreach ($relationships as $relationship) {
            // Find supplier category
            $supplierCategory = $supplierCategories->firstWhere('CategoryName', $relationship['supplier_category_name']);

            if (!$supplierCategory) {
                // Create supplier category if it doesn't exist
                $supplierCategoryId = DB::table('t_SupplierCategories')->insertGetId([
                    'CategoryName' => $relationship['supplier_category_name'],
                    'Description' => "Suppliers for {$relationship['supplier_category_name']}",
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                ]);

                $this->command->info("   Created supplier category: {$relationship['supplier_category_name']} (ID: {$supplierCategoryId})");
            } else {
                $supplierCategoryId = $supplierCategory->SupplierCategoryID;
            }

            // Link to item categories
            foreach ($relationship['item_categories'] as $itemCategoryName) {
                $itemCategory = $itemCategories->firstWhere('Name', $itemCategoryName);

                if (!$itemCategory) {
                    $this->command->warn("   ⚠️  Item category '{$itemCategoryName}' not found in existing main categories. Skipping.");
                    continue; // Skip this item category
                } else {
                    $itemCategoryId = $itemCategory->Id;
                }

                // Check if relationship already exists
                $exists = DB::table('t_SupplierCategory_ItemCategory')
                    ->where('SupplierCategoryID', $supplierCategoryId)
                    ->where('ItemCategoryID', $itemCategoryId)
                    ->whereNull('DeletedOn')
                    ->exists();

                if (!$exists) {
                    DB::table('t_SupplierCategory_ItemCategory')->insert([
                        'SupplierCategoryID' => $supplierCategoryId,
                        'ItemCategoryID' => $itemCategoryId,
                        'CreatedBy' => 1,
                        'CreatedOn' => $now,
                    ]);

                    $created++;
                    $this->command->info("   ✅ Linked: {$relationship['supplier_category_name']} -> {$itemCategoryName}");
                }
            }
        }

        // Now create some third party -> supplier category relationships
        $this->createThirdPartySupplierCategoryRelationships($supplierCategories);

        $this->command->info("✅ Created {$created} new supplier-category to item-category relationships!");
    }

    private function createThirdPartySupplierCategoryRelationships($supplierCategories)
    {
        $this->command->info('🏢 Creating Third Party -> Supplier Category relationships...');

        // Get some active third parties (Status might be 'A' for Active)
        $thirdParties = DB::table('t_ThirdParties')
            ->where('Status', 'A') // Try 'A' for Active
            ->take(10) // Limit to first 10 active suppliers
            ->get();

        if ($thirdParties->isEmpty()) {
            // Try numeric status if 'A' doesn't work
            $thirdParties = DB::table('t_ThirdParties')
                ->where('Status', 1)
                ->take(10)
                ->get();
        }

        if ($thirdParties->isEmpty()) {
            $this->command->warn('   ⚠️  No active third parties found');
            return;
        }

        $linked = 0;

        foreach ($thirdParties as $index => $thirdParty) {
            // Assign suppliers to categories in a round-robin fashion
            $supplierCategory = $supplierCategories->get($index % $supplierCategories->count());

            // Check if relationship exists
            $exists = DB::table('t_ThirdParty_SupplierCategory')
                ->where('third_party_id', $thirdParty->Id)
                ->where('supplier_category_id', $supplierCategory->SupplierCategoryID)
                ->exists();

            if (!$exists) {
                DB::table('t_ThirdParty_SupplierCategory')->insert([
                    'third_party_id' => $thirdParty->Id,
                    'supplier_category_id' => $supplierCategory->SupplierCategoryID,
                ]);

                $supplierName = $thirdParty->TradingName ?: $thirdParty->ThirdPartyName;
                $linked++;
                $this->command->info("   ✅ Linked: {$supplierName} -> {$supplierCategory->CategoryName}");
            }
        }

        $this->command->info("✅ Created {$linked} new third party -> supplier category relationships!");
    }
}
