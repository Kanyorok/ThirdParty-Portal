<?php

/**
 * Quick Workflow Transfer Script
 * 
 * This script combines export and import in one operation for quick transfers
 * between databases. It connects to both databases and copies workflows directly.
 * 
 * Usage: php scripts/quick_workflow_transfer.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         BR_ERP Quick Workflow Transfer Wizard             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "This wizard will help you transfer workflows between databases.\n\n";

// Step 1: Choose operation
echo "Choose operation:\n";
echo "  1. Export workflows to file\n";
echo "  2. Import workflows from file\n";
echo "  3. Quick transfer (export then import)\n";
echo "\nEnter choice (1-3): ";
$choice = trim(fgets(STDIN));

if ($choice == '1') {
    // Export only
    echo "\nEnter output filename (press Enter for default): ";
    $filename = trim(fgets(STDIN));
    if (empty($filename)) {
        $filename = 'workflows_export_' . date('Y-m-d_His') . '.json';
    }
    
    echo "\nExecuting export...\n";
    passthru("php " . __DIR__ . "/export_workflows.php " . escapeshellarg($filename), $exitCode);
    exit($exitCode);
    
} elseif ($choice == '2') {
    // Import only
    echo "\nEnter input filename: ";
    $filename = trim(fgets(STDIN));
    
    if (empty($filename) || !file_exists($filename)) {
        echo "❌ Error: File not found\n";
        exit(1);
    }
    
    echo "\nOverwrite existing workflows? (yes/no): ";
    $overwrite = trim(fgets(STDIN));
    $forceFlag = (strtolower($overwrite) === 'yes' || strtolower($overwrite) === 'y') ? '--force' : '';
    
    echo "\nExecuting import...\n";
    passthru("php " . __DIR__ . "/import_workflows.php " . escapeshellarg($filename) . " " . $forceFlag, $exitCode);
    exit($exitCode);
    
} elseif ($choice == '3') {
    // Quick transfer
    echo "\n╔════════════════════════════════════════════════════════════╗\n";
    echo "║                 Quick Transfer Mode                        ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
    echo "This will:\n";
    echo "  1. Export workflows from CURRENT database\n";
    echo "  2. Let you switch to DESTINATION database\n";
    echo "  3. Import workflows into destination\n\n";
    
    // Step 1: Export
    $tempFile = sys_get_temp_dir() . '/workflows_transfer_' . uniqid() . '.json';
    echo "Step 1: Exporting from current database...\n";
    echo "Current DB: " . env('DB_DATABASE') . "@" . env('DB_HOST') . "\n\n";
    
    passthru("php " . __DIR__ . "/export_workflows.php " . escapeshellarg($tempFile), $exitCode);
    
    if ($exitCode !== 0) {
        echo "\n❌ Export failed!\n";
        exit(1);
    }
    
    echo "\n✅ Export completed!\n";
    echo "Exported to: {$tempFile}\n\n";
    
    // Step 2: Instructions for database switch
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  NEXT: Switch to your DESTINATION database                ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Instructions:\n";
    echo "  1. Edit your .env file\n";
    echo "  2. Update DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD\n";
    echo "  3. Save the .env file\n";
    echo "  4. Come back here and press Enter\n\n";
    
    echo "Temporary file location: {$tempFile}\n";
    echo "Save this path if you need it!\n\n";
    
    echo "Press Enter when you've switched databases...";
    fgets(STDIN);
    
    // Step 3: Import
    echo "\nStep 2: Importing into destination database...\n";
    echo "Current DB: " . env('DB_DATABASE') . "@" . env('DB_HOST') . "\n\n";
    
    echo "Overwrite existing workflows? (yes/no): ";
    $overwrite = trim(fgets(STDIN));
    $forceFlag = (strtolower($overwrite) === 'yes' || strtolower($overwrite) === 'y') ? '--force' : '';
    
    passthru("php " . __DIR__ . "/import_workflows.php " . escapeshellarg($tempFile) . " " . $forceFlag, $exitCode);
    
    if ($exitCode === 0) {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║         ✅ Transfer completed successfully!                ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";
    } else {
        echo "\n❌ Import failed!\n";
        echo "The export file is still available at: {$tempFile}\n";
        echo "You can try importing again manually:\n";
        echo "  php scripts/import_workflows.php {$tempFile}\n";
    }
    
    // Cleanup
    if (file_exists($tempFile)) {
        echo "\nDelete temporary file? (yes/no): ";
        $delete = trim(fgets(STDIN));
        if (strtolower($delete) === 'yes' || strtolower($delete) === 'y') {
            unlink($tempFile);
            echo "✓ Temporary file deleted\n";
        } else {
            echo "✓ Temporary file kept at: {$tempFile}\n";
        }
    }
    
    exit($exitCode);
    
} else {
    echo "❌ Invalid choice\n";
    exit(1);
}
