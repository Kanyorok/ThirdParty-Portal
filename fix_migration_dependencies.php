<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING MIGRATION DEPENDENCIES ===\n";

try {
    // Step 1: Check and create critical tables in order
    $criticalTables = [
        't_Images' => 'CREATE TABLE t_Images (ImageID int IDENTITY(1,1) PRIMARY KEY, [other_cols_placeholder] varchar(255))',
        't_ItemCategories' => 'CREATE TABLE t_ItemCategories (Id int IDENTITY(1,1) PRIMARY KEY, [CategoryName] varchar(255))',
    ];
    
    echo "1. Creating missing dependency tables:\n";
    foreach ($criticalTables as $tableName => $sql) {
        try {
            $exists = DB::getSchemaBuilder()->hasTable($tableName);
            if (!$exists) {
                echo "   - Creating $tableName: ";
                // Note: This is a simplified approach, we'll use actual migrations below
                echo "Will be created by migration\n";
            } else {
                echo "   - $tableName: ✅ Already exists\n";
            }
        } catch (Exception $e) {
            echo "   - $tableName: ❌ Error checking - " . $e->getMessage() . "\n";
        }
    }
    
    // Step 2: Try to run specific migrations in dependency order
    echo "\n2. Running critical migrations in order:\n";
    $orderedMigrations = [
        'database/migrations/2025_04_11_101654_create_t_items_categories_table.php',
        'database/migrations/2025_04_11_103156_create_t_items_table.php',
        'database/migrations/2025_04_19_090733_create_suppliers_table.php',
        'database/migrations/2025_04_20_000000_create_procurement_periods_table.php',
        'database/migrations/2025_04_21_132746_create_procurement_period_supplier_table.php',
    ];
    
    foreach ($orderedMigrations as $migration) {
        $name = basename($migration);
        echo "   - $name: ";
        try {
            // Execute the migration file
            $class = include $migration;
            if (method_exists($class, 'up')) {
                $class->up();
                echo "✅ Success\n";
            } else {
                echo "❌ No up() method\n";
            }
        } catch (Exception $e) {
            echo "❌ Error - " . $e->getMessage() . "\n";
            
            // Check if it's a dependency issue
            if (strpos($e->getMessage(), 'references invalid table') !== false) {
                echo "      ^^ This looks like a foreign key dependency issue\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== DEPENDENCY FIX COMPLETE ===\n";
