<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MIGRATION & SEEDING TEST ===\n";

try {
    // Test database connection
    echo "1. Database Connection: ";
    $pdo = DB::connection()->getPdo();
    echo "✅ Connected\n";
    
    // Check if tables exist
    echo "\n2. Key Tables Check:\n";
    $tables = ['t_Users', 't_Tenders', 't_TenderAwards', 't_Suppliers', 't_BidResponsiveness'];
    
    foreach ($tables as $table) {
        try {
            $exists = DB::getSchemaBuilder()->hasTable($table);
            echo "   - $table: " . ($exists ? "✅ Exists" : "❌ Missing") . "\n";
        } catch (Exception $e) {
            echo "   - $table: ❌ Error - " . $e->getMessage() . "\n";
        }
    }
    
    // Check record counts
    echo "\n3. Record Counts:\n";
    $models = [
        'Users' => 'App\Models\Auth\User',
        'Tenders' => 'App\Models\Procurement\Tender',
        'Suppliers' => 'App\Models\ThirdParies\Supplier',
        'TenderAwards' => 'App\Models\Procurement\TenderAward'
    ];
    
    foreach ($models as $name => $class) {
        try {
            if (class_exists($class)) {
                $count = $class::count();
                echo "   - $name: $count records\n";
            } else {
                echo "   - $name: ❌ Model not found\n";
            }
        } catch (Exception $e) {
            echo "   - $name: ❌ Error - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n4. Migration Status: ";
    // Check migrations table
    try {
        $migrationsCount = DB::table('migrations')->count();
        echo "✅ $migrationsCount migrations applied\n";
    } catch (Exception $e) {
        echo "❌ Error - " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
