<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING MIGRATION STATE ===\n";

try {
    // Step 1: Ensure migrations table exists
    echo "1. Checking migrations table:\n";
    
    $hasOldMigrations = false;
    $hasNewMigrations = false;
    
    try {
        $count = DB::table('t_Migrations')->count();
        echo "   - Found t_Migrations table with $count records\n";
        $hasOldMigrations = true;
    } catch (Exception $e) {
        echo "   - No t_Migrations table found\n";
    }
    
    try {
        $count = DB::table('migrations')->count();
        echo "   - Found migrations table with $count records\n";
        $hasNewMigrations = true;
    } catch (Exception $e) {
        echo "   - No migrations table found\n";
    }
    
    // Step 2: If we have the old table, copy data to new one
    if ($hasOldMigrations && !$hasNewMigrations) {
        echo "   - Creating new migrations table and copying data...\n";
        DB::statement("CREATE TABLE migrations (id int IDENTITY(1,1) PRIMARY KEY, migration varchar(255) NOT NULL, batch int NOT NULL)");
        
        $oldMigrations = DB::table('t_Migrations')->get();
        foreach ($oldMigrations as $migration) {
            DB::table('migrations')->insert([
                'migration' => $migration->migration,
                'batch' => $migration->batch
            ]);
        }
        echo "   - Copied " . $oldMigrations->count() . " migration records\n";
    }
    
    // Step 3: Mark completed migrations
    echo "\n2. Recording completed migrations:\n";
    $completedMigrations = [
        // Base migrations that should exist
        '0001_01_01_000001_create_users_table',
        '0001_01_01_000002_create_jobs_table', 
        '0001_01_01_000003_create_cache_table',
        
        // Recently created ones
        '2025_04_11_101654_create_t_items_categories_table',
        '2025_04_19_090733_create_suppliers_table',
        '2025_04_20_000000_create_procurement_periods_table',
        '2025_04_21_132746_create_procurement_period_supplier_table',
    ];
    
    $currentBatch = DB::table('migrations')->max('batch') + 1;
    
    foreach ($completedMigrations as $migration) {
        $exists = DB::table('migrations')->where('migration', $migration)->exists();
        if (!$exists) {
            try {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $currentBatch
                ]);
                echo "   - Recorded: $migration\n";
            } catch (Exception $e) {
                echo "   - Failed to record $migration: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   - Already recorded: $migration\n";
        }
    }
    
    echo "\n3. Checking table existence vs migration records:\n";
    $tablesToCheck = [
        't_Users' => '0001_01_01_000001_create_users_table',
        't_ItemCategories' => '2025_04_11_101654_create_t_items_categories_table', 
        't_Items' => '2025_04_11_103156_create_t_items_table',
        't_Suppliers' => '2025_04_19_090733_create_suppliers_table',
        't_ProcurementPeriods' => '2025_04_20_000000_create_procurement_periods_table',
    ];
    
    foreach ($tablesToCheck as $table => $migration) {
        $tableExists = DB::getSchemaBuilder()->hasTable($table);
        $migrationExists = DB::table('migrations')->where('migration', $migration)->exists();
        
        echo "   - $table: " . ($tableExists ? "✅ Table exists" : "❌ Missing table") . 
             " | " . ($migrationExists ? "✅ Migration recorded" : "❌ Migration not recorded") . "\n";
             
        // If table exists but migration not recorded, record it
        if ($tableExists && !$migrationExists) {
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $currentBatch
            ]);
            echo "     ^^ Fixed: Recorded migration for existing table\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== MIGRATION STATE FIX COMPLETE ===\n";
