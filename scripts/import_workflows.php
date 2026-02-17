<?php

/**
 * Import Approval Workflows Script
 * 
 * This script imports workflow configurations from a JSON file exported
 * by export_workflows.php into the current database.
 * 
 * Usage: php scripts/import_workflows.php <input_file> [--force]
 * Example: php scripts/import_workflows.php workflows_backup.json
 * Example: php scripts/import_workflows.php workflows_backup.json --force (overwrite existing)
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get input file and options from command line
if (!isset($argv[1])) {
    echo "❌ Error: No input file specified\n";
    echo "Usage: php scripts/import_workflows.php <input_file> [--force]\n";
    exit(1);
}

$inputFile = $argv[1];
$force = in_array('--force', $argv);

if (!file_exists($inputFile)) {
    echo "❌ Error: File not found: {$inputFile}\n";
    exit(1);
}

echo "Starting workflow import...\n";
echo "Input file: {$inputFile}\n";
echo "Force mode: " . ($force ? "YES (will overwrite existing)" : "NO") . "\n\n";

try {
    // Read and parse JSON file
    echo "Reading import file...\n";
    $json = file_get_contents($inputFile);
    $import = json_decode($json, true);

    if (!$import) {
        throw new Exception("Invalid JSON file");
    }

    echo "  ✓ File loaded successfully\n";
    echo "  Exported at: {$import['exported_at']}\n";
    echo "  Version: {$import['version']}\n\n";

    $data = $import['data'];
    $actorId = DB::table('t_Users')->min('Id') ?? 1; // Use first user as actor
    $now = now();

    // Start transaction
    DB::beginTransaction();

    try {
        // 1. Import Workflow Types
        echo "Importing workflow types...\n";
        foreach ($data['workflow_types'] as $type) {
            $existing = DB::table('t_WorkFlowTypes')
                ->where('TypeID', $type['TypeID'])
                ->first();

            if ($existing) {
                if ($force) {
                    DB::table('t_WorkFlowTypes')
                        ->where('TypeID', $type['TypeID'])
                        ->update([
                            'Name' => $type['Name'],
                            'ModifiedBy' => $actorId,
                            'ModifiedOn' => $now,
                        ]);
                    echo "  ✓ Updated: {$type['Name']}\n";
                } else {
                    echo "  ⊘ Skipped (exists): {$type['Name']}\n";
                }
            } else {
                DB::table('t_WorkFlowTypes')->insert([
                    'TypeID' => $type['TypeID'],
                    'Name' => $type['Name'],
                    'CreatedBy' => $actorId,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actorId,
                    'ModifiedOn' => $now,
                ]);
                echo "  ✓ Created: {$type['Name']}\n";
            }
        }

        // 2. Import Code Details
        echo "\nImporting code details...\n";
        foreach ($data['code_details'] as $codeDetail) {
            $existing = DB::table('t_CodeDetails')
                ->where('CodeID', $codeDetail['CodeID'])
                ->where('Value', $codeDetail['Value'])
                ->first();

            if ($existing) {
                if ($force) {
                    DB::table('t_CodeDetails')
                        ->where('CodeID', $codeDetail['CodeID'])
                        ->where('Value', $codeDetail['Value'])
                        ->update([
                            'Description' => $codeDetail['Description'],
                            'DisplayOrder' => $codeDetail['DisplayOrder'],
                            'IsActive' => $codeDetail['IsActive'],
                            'ModifiedBy' => $actorId,
                            'ModifiedOn' => $now,
                        ]);
                    echo "  ✓ Updated: {$codeDetail['CodeID']} - {$codeDetail['Description']}\n";
                } else {
                    echo "  ⊘ Skipped (exists): {$codeDetail['CodeID']} - {$codeDetail['Description']}\n";
                }
            } else {
                DB::table('t_CodeDetails')->insert([
                    'CodeID' => $codeDetail['CodeID'],
                    'Description' => $codeDetail['Description'],
                    'Value' => $codeDetail['Value'],
                    'DisplayOrder' => $codeDetail['DisplayOrder'],
                    'IsActive' => $codeDetail['IsActive'],
                    'CreatedBy' => $actorId,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actorId,
                    'ModifiedOn' => $now,
                ]);
                echo "  ✓ Created: {$codeDetail['CodeID']} - {$codeDetail['Description']}\n";
            }
        }

        // 3. Import Workflows
        echo "\nImporting workflows...\n";
        foreach ($data['workflows'] as $workflow) {
            echo "  Processing: {$workflow['Name']}...\n";
            
            $existing = DB::table('t_WorkFlows')
                ->where('Source', $workflow['Source'])
                ->where('Name', $workflow['Name'])
                ->first();

            if ($existing && !$force) {
                echo "    ⊘ Skipped (exists). Use --force to overwrite\n";
                continue;
            }

            if ($existing && $force) {
                // Delete existing workflow and related data
                $stageIds = DB::table('t_WorkFlowStages')
                    ->where('WorkFlowId', $existing->Id)
                    ->pluck('Id');
                
                // Delete limits for these stages
                if ($stageIds->isNotEmpty()) {
                    DB::table('t_WorkFlowLimits')
                        ->whereIn('WorkFlowStageId', $stageIds)
                        ->delete();
                }
                
                DB::table('t_WorkFlowStages')->where('WorkFlowId', $existing->Id)->delete();
                DB::table('t_WorkFlows')->where('Id', $existing->Id)->delete();
                echo "    ⊘ Deleted existing workflow\n";
            }

            // Create workflow
            $workflowId = DB::table('t_WorkFlows')->insertGetId([
                'Name' => $workflow['Name'],
                'Source' => $workflow['Source'],
                'FinalStage' => $workflow['FinalStage'],
                'Description' => $workflow['Description'],
                'CreatedBy' => $actorId,
                'CreatedOn' => $now,
                'ModifiedBy' => $actorId,
                'ModifiedOn' => $now,
            ]);
            echo "    ✓ Created workflow (ID: {$workflowId})\n";

            // Import stages
            foreach ($workflow['Stages'] as $stage) {
                // Get workflow type ID
                $workflowType = DB::table('t_WorkFlowTypes')
                    ->where('TypeID', $stage['WorkFlowTypeID'])
                    ->first();

                if (!$workflowType) {
                    echo "      ⚠ Warning: Workflow type not found: {$stage['WorkFlowTypeID']}\n";
                    continue;
                }

                // Get permission ID
                $permissionId = null;
                if ($stage['PermissionName']) {
                    $permission = DB::table('t_Permissions')
                        ->where('name', $stage['PermissionName'])
                        ->first();
                    
                    if ($permission) {
                        $permissionId = $permission->id;
                    } else {
                        echo "      ⚠ Warning: Permission not found: {$stage['PermissionName']}\n";
                    }
                }

                // Get status ID
                $statusId = null;
                if ($stage['StatusCodeID'] && $stage['StatusValue']) {
                    $status = DB::table('t_CodeDetails')
                        ->where('CodeID', $stage['StatusCodeID'])
                        ->where('Value', $stage['StatusValue'])
                        ->first();
                    
                    if ($status) {
                        $statusId = $status->ID;
                    }
                }

                $stageId = DB::table('t_WorkFlowStages')->insertGetId([
                    'Order' => $stage['Order'],
                    'StageName' => $stage['StageName'],
                    'EscalationLimit' => $stage['EscalationLimit'],
                    'WorkFlowId' => $workflowId,
                    'WorkFlowTypeId' => $workflowType->Id,
                    'PermissionId' => $permissionId,
                    'Count' => $stage['Count'],
                    'StatusId' => $statusId,
                    'CreatedBy' => $actorId,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actorId,
                    'ModifiedOn' => $now,
                ]);
                echo "      ✓ Created stage: {$stage['StageName']} (Order: {$stage['Order']})\n";
                
                // Import limits for this stage
                if (isset($stage['Limits']) && is_array($stage['Limits'])) {
                    foreach ($stage['Limits'] as $limit) {
                        $limitPermission = DB::table('t_Permissions')
                            ->where('name', $limit['PermissionName'])
                            ->first();
                        
                        if (!$limitPermission) {
                            echo "        ⚠ Warning: Limit permission not found: {$limit['PermissionName']}\n";
                            continue;
                        }
                        
                        DB::table('t_WorkFlowLimits')->insert([
                            'MaxAmount' => $limit['MaxAmount'],
                            'PermissionId' => $limitPermission->id,
                            'WorkFlowStageId' => $stageId,
                            'CreatedBy' => $actorId,
                            'CreatedOn' => $now,
                            'ModifiedBy' => $actorId,
                            'ModifiedOn' => $now,
                        ]);
                        echo "        ✓ Created limit (Amount: {$limit['MaxAmount']})\n";
                    }
                }
            }
        }

        // Commit transaction
        DB::commit();

        echo "\n✅ Import completed successfully!\n\n";

        // Summary
        echo "Summary:\n";
        echo "  - Workflow Types: " . count($data['workflow_types']) . "\n";
        echo "  - Code Details: " . count($data['code_details']) . "\n";
        echo "  - Workflows: " . count($data['workflows']) . "\n";

    } catch (Exception $e) {
        DB::rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
