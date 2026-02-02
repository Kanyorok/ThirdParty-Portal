<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear all existing module keys first
        DB::table('t_Modules')->update(['ModuleKey' => null]);

        // ONLY update parent modules (ParentID is null) with license keys
        $parentModuleKeyMappings = [
            100000 => 'THIRDPARTY',
            200000 => 'CRM',
            300000 => 'PROCUREMENT',
            400000 => 'INVENTORY',
            500000 => 'PROPERTY',
            600000 => 'FLEET',
            700000 => 'DMS',
            800000 => 'LEGAL',
            900000 => 'INSURANCE',
            1000000 => 'HRM',
            1100000 => 'FINANCE',
            1200000 => 'BUDGET',
            9800000 => 'SETTINGS',
            9900000 => 'ACCOUNT',
        ];

        $updated = 0;
        foreach ($parentModuleKeyMappings as $moduleId => $moduleKey) {
            $result = DB::table('t_Modules')
                ->where('ModuleID', $moduleId)
                ->whereNull('ParentID') // Ensure it's a parent module
                ->update(['ModuleKey' => $moduleKey]);

            if ($result > 0) {
                $updated++;
                $this->command->info("✅ Updated parent module: {$moduleKey} (ID: {$moduleId})");
            }
        }

        $this->command->info("Updated {$updated} parent modules with license keys.");

        // Verify the parent modules
        $parentModulesWithKeys = DB::table('t_Modules')
            ->whereNull('ParentID')
            ->whereNotNull('ModuleKey')
            ->select('ModuleID', 'Name', 'ModuleKey')
            ->orderBy('ModuleID')
            ->get();

        $this->command->info("\n📋 Parent Modules with License Keys:");
        $this->command->info(str_repeat("-", 70));
        foreach ($parentModulesWithKeys as $module) {
            $this->command->info("  {$module->ModuleKey} | {$module->Name} (ID: {$module->ModuleID})");
        }

        // Count sub-modules (these will inherit access from parents)
        $subModuleCount = DB::table('t_Modules')
            ->whereNotNull('ParentID')
            ->count();

        $this->command->info("\n📊 Summary:");
        $this->command->info("  - Parent modules with license keys: {$parentModulesWithKeys->count()}");
        $this->command->info("  - Sub-modules (inherit from parent): {$subModuleCount}");
        $this->command->info("  - Total modules: " . ($parentModulesWithKeys->count() + $subModuleCount));
        $this->command->info("\n✅ Parent module licensing setup completed!");
        $this->command->info("ℹ️  Sub-modules automatically inherit access from their licensed parent module.");
    }
}
