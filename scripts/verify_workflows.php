<?php

/**
 * Workflow Diagnostic and Verification Script
 * 
 * This script helps diagnose issues with workflow export/import by:
 * - Checking database connection
 * - Counting workflows and stages
 * - Verifying exported files
 * - Comparing source and destination databases
 * 
 * Usage: php scripts/verify_workflows.php [export_file.json]
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║        Workflow Diagnostic & Verification Tool            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Step 1: Database Connection Info
echo "1️⃣  DATABASE CONNECTION\n";
echo str_repeat("─", 60) . "\n";
try {
    $dbHost = env('DB_HOST');
    $dbName = env('DB_DATABASE');
    $dbDriver = env('DB_CONNECTION');
    
    echo "  Connection: {$dbDriver}\n";
    echo "  Host: {$dbHost}\n";
    echo "  Database: {$dbName}\n";
    
    // Test connection
    DB::connection()->getPdo();
    echo "  ✅ Status: CONNECTED\n\n";
} catch (Exception $e) {
    echo "  ❌ Status: FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Step 2: Workflow Count
echo "2️⃣  WORKFLOW INVENTORY\n";
echo str_repeat("─", 60) . "\n";

try {
    $totalWorkflows = DB::table('t_WorkFlows')->whereNull('DeletedOn')->count();
    $deletedWorkflows = DB::table('t_WorkFlows')->whereNotNull('DeletedOn')->count();
    
    echo "  Total Active Workflows: {$totalWorkflows}\n";
    echo "  Deleted Workflows: {$deletedWorkflows}\n";
    
    if ($totalWorkflows == 0) {
        echo "  ⚠️  WARNING: No workflows found in database!\n";
    } else {
        echo "  ✅ Workflows found\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n\n";
}

// Step 3: Workflow Details
echo "3️⃣  WORKFLOW BREAKDOWN\n";
echo str_repeat("─", 60) . "\n";

try {
    $workflows = DB::table('t_WorkFlows')
        ->whereNull('DeletedOn')
        ->orderBy('Id')
        ->get();
    
    $workflowsWithStages = 0;
    $workflowsWithoutStages = 0;
    $totalStages = 0;
    
    foreach ($workflows as $wf) {
        $stageCount = DB::table('t_WorkFlowStages')
            ->where('WorkFlowId', $wf->Id)
            ->whereNull('DeletedOn')
            ->count();
        
        $totalStages += $stageCount;
        
        if ($stageCount > 0) {
            $workflowsWithStages++;
        } else {
            $workflowsWithoutStages++;
        }
    }
    
    echo "  Workflows with stages: {$workflowsWithStages}\n";
    echo "  Workflows without stages: {$workflowsWithoutStages}\n";
    echo "  Total stages: {$totalStages}\n";
    
    if ($workflowsWithoutStages > 0) {
        echo "  ⚠️  WARNING: {$workflowsWithoutStages} workflow(s) have no stages!\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n\n";
}

// Step 4: Detailed Workflow List
echo "4️⃣  WORKFLOW DETAILS\n";
echo str_repeat("─", 60) . "\n";
printf("  %-5s %-40s %-10s\n", "ID", "Name", "Stages");
echo "  " . str_repeat("─", 58) . "\n";

try {
    $workflows = DB::table('t_WorkFlows')
        ->whereNull('DeletedOn')
        ->orderBy('Id')
        ->get();
    
    foreach ($workflows as $wf) {
        $stageCount = DB::table('t_WorkFlowStages')
            ->where('WorkFlowId', $wf->Id)
            ->whereNull('DeletedOn')
            ->count();
        
        $name = strlen($wf->Name) > 40 ? substr($wf->Name, 0, 37) . '...' : $wf->Name;
        $indicator = $stageCount == 0 ? '⚠️ ' : '  ';
        
        printf("  %-5s %-40s %s%-10s\n", $wf->Id, $name, $indicator, $stageCount);
    }
    echo "\n";
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n\n";
}

// Step 5: Check for missing related data
echo "5️⃣  DATA INTEGRITY CHECK\n";
echo str_repeat("─", 60) . "\n";

try {
    // Check workflow types
    $workflowTypes = DB::table('t_WorkFlowTypes')->whereNull('DeletedOn')->count();
    echo "  Workflow Types: {$workflowTypes} " . ($workflowTypes >= 4 ? "✅" : "⚠️") . "\n";
    
    // Check permissions
    $permissions = DB::table('t_Permissions')->count();
    echo "  Permissions: {$permissions} " . ($permissions > 0 ? "✅" : "⚠️") . "\n";
    
    // Check code details
    $codeDetails = DB::table('t_CodeDetails')
        ->where(function($q) {
            $q->where('CodeID', 'LIKE', '%Approval%')
              ->orWhere('CodeID', 'LIKE', '%WorkFlow%');
        })
        ->whereNull('DeletedOn')
        ->count();
    echo "  Approval Code Details: {$codeDetails} " . ($codeDetails > 0 ? "✅" : "⚠️") . "\n";
    
    // Check workflow limits
    $limits = DB::table('t_WorkFlowLimits')->whereNull('DeletedOn')->count();
    echo "  Workflow Limits: {$limits}\n";
    
    echo "\n";
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n\n";
}

// Step 6: Verify export file if provided
if (isset($argv[1])) {
    $exportFile = $argv[1];
    
    echo "6️⃣  EXPORT FILE VERIFICATION\n";
    echo str_repeat("─", 60) . "\n";
    echo "  File: {$exportFile}\n";
    
    if (!file_exists($exportFile)) {
        echo "  ❌ Status: FILE NOT FOUND\n\n";
    } else {
        $fileSize = filesize($exportFile);
        echo "  Size: " . number_format($fileSize) . " bytes (" . number_format($fileSize / 1024, 2) . " KB)\n";
        
        try {
            $content = file_get_contents($exportFile);
            $data = json_decode($content, true);
            
            if (!$data) {
                echo "  ❌ Status: INVALID JSON\n\n";
            } else {
                echo "  ✅ Status: VALID JSON\n";
                echo "  Exported at: " . ($data['exported_at'] ?? 'unknown') . "\n";
                echo "  Version: " . ($data['version'] ?? 'unknown') . "\n";
                
                if (isset($data['data'])) {
                    $workflowsInFile = count($data['data']['workflows'] ?? []);
                    $typesInFile = count($data['data']['workflow_types'] ?? []);
                    $codesInFile = count($data['data']['code_details'] ?? []);
                    
                    echo "\n  Content:\n";
                    echo "    - Workflow Types: {$typesInFile}\n";
                    echo "    - Workflows: {$workflowsInFile}\n";
                    echo "    - Code Details: {$codesInFile}\n";
                    
                    // Compare with database
                    echo "\n  Comparison:\n";
                    $dbWorkflows = DB::table('t_WorkFlows')->whereNull('DeletedOn')->count();
                    $diff = $dbWorkflows - $workflowsInFile;
                    
                    if ($diff == 0) {
                        echo "    ✅ File matches database ({$workflowsInFile} workflows)\n";
                    } elseif ($diff > 0) {
                        echo "    ⚠️  File has FEWER workflows than database\n";
                        echo "       Database: {$dbWorkflows}, File: {$workflowsInFile}\n";
                        echo "       Missing: {$diff} workflow(s)\n";
                    } else {
                        echo "    ⚠️  File has MORE workflows than database\n";
                        echo "       Database: {$dbWorkflows}, File: {$workflowsInFile}\n";
                    }
                    
                    // List workflows in file
                    if ($workflowsInFile > 0) {
                        echo "\n  Workflows in export file:\n";
                        foreach ($data['data']['workflows'] as $wf) {
                            $stageCount = count($wf['Stages'] ?? []);
                            echo "    - {$wf['Name']} ({$stageCount} stages)\n";
                        }
                    }
                }
                echo "\n";
            }
        } catch (Exception $e) {
            echo "  ❌ Error reading file: " . $e->getMessage() . "\n\n";
        }
    }
}

// Step 7: Recommendations
echo "7️⃣  RECOMMENDATIONS\n";
echo str_repeat("─", 60) . "\n";

$issues = [];
$recommendations = [];

// Check for workflows without stages
$workflowsWithoutStages = DB::table('t_WorkFlows as wf')
    ->leftJoin('t_WorkFlowStages as wfs', function($join) {
        $join->on('wf.Id', '=', 'wfs.WorkFlowId')
             ->whereNull('wfs.DeletedOn');
    })
    ->whereNull('wf.DeletedOn')
    ->whereNull('wfs.Id')
    ->count();

if ($workflowsWithoutStages > 0) {
    $issues[] = "{$workflowsWithoutStages} workflow(s) have no stages";
    $recommendations[] = "Review and add stages to incomplete workflows before exporting";
}

// Check total workflows
$totalWorkflows = DB::table('t_WorkFlows')->whereNull('DeletedOn')->count();
if ($totalWorkflows == 0) {
    $issues[] = "No workflows found in database";
    $recommendations[] = "Run workflow seeders or import from backup";
} elseif ($totalWorkflows < 5) {
    $issues[] = "Very few workflows ({$totalWorkflows}) - may not be production data";
    $recommendations[] = "Verify you're connected to the correct database";
}

// Check if export file was provided and analyzed
if (isset($argv[1]) && file_exists($argv[1])) {
    $content = file_get_contents($argv[1]);
    $data = json_decode($content, true);
    $workflowsInFile = count($data['data']['workflows'] ?? []);
    
    if ($workflowsInFile < $totalWorkflows) {
        $issues[] = "Export file is incomplete ({$workflowsInFile}/{$totalWorkflows} workflows)";
        $recommendations[] = "Re-run export: php scripts/export_workflows.php complete_workflows.json";
    }
}

if (empty($issues)) {
    echo "  ✅ No issues detected!\n";
    echo "  Your workflows are ready to export/import.\n\n";
} else {
    echo "  ⚠️  Issues Found:\n";
    foreach ($issues as $issue) {
        echo "    - {$issue}\n";
    }
    echo "\n  💡 Recommendations:\n";
    foreach ($recommendations as $rec) {
        echo "    • {$rec}\n";
    }
    echo "\n";
}

// Step 8: Quick Actions
echo "8️⃣  QUICK ACTIONS\n";
echo str_repeat("─", 60) . "\n";
echo "  Export all workflows:\n";
echo "    php scripts/export_workflows.php my_workflows.json\n\n";
echo "  Verify export file:\n";
echo "    php scripts/verify_workflows.php my_workflows.json\n\n";
echo "  Import workflows (skip existing):\n";
echo "    php scripts/import_workflows.php my_workflows.json\n\n";
echo "  Import workflows (overwrite existing):\n";
echo "    php scripts/import_workflows.php my_workflows.json --force\n\n";

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                  Diagnostic Complete                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
