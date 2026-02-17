<?php

/**
 * Export Approval Workflows Script
 * 
 * This script exports all workflow configurations from the current database
 * into a portable JSON file that can be imported into other databases.
 * 
 * Usage: php scripts/export_workflows.php [output_file]
 * Example: php scripts/export_workflows.php workflows_backup.json
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get output file from command line or use default
$outputFile = $argv[1] ?? 'workflows_export_' . date('Y-m-d_His') . '.json';

echo "Starting workflow export...\n";
echo "Output file: {$outputFile}\n\n";

try {
    $export = [
        'exported_at' => now()->toDateTimeString(),
        'version' => '1.0',
        'description' => 'BR_ERP Approval Workflows Export',
        'data' => []
    ];

    // 1. Export Workflow Types
    echo "Exporting workflow types...\n";
    $workflowTypes = DB::table('t_WorkFlowTypes')
        ->whereNull('DeletedOn')
        ->get(['TypeID', 'Name'])
        ->toArray();
    
    $export['data']['workflow_types'] = $workflowTypes;
    echo "  ✓ Exported " . count($workflowTypes) . " workflow types\n";

    // 2. Export Workflows with their stages and limits
    echo "\nExporting workflows...\n";
    $workflows = DB::table('t_WorkFlows')
        ->whereNull('DeletedOn')
        ->get(['Id', 'Name', 'Source', 'FinalStage', 'Description'])
        ->toArray();

    $workflowData = [];
    foreach ($workflows as $workflow) {
        echo "  Processing: {$workflow->Name}...\n";
        
        // Get stages for this workflow
        $stages = DB::table('t_WorkFlowStages as wfs')
            ->leftJoin('t_WorkFlowTypes as wft', 'wfs.WorkFlowTypeId', '=', 'wft.Id')
            ->leftJoin('t_Permissions as p', 'wfs.PermissionId', '=', 'p.id')
            ->leftJoin('t_CodeDetails as cd', 'wfs.StatusId', '=', 'cd.ID')
            ->where('wfs.WorkFlowId', $workflow->Id)
            ->whereNull('wfs.DeletedOn')
            ->orderBy('wfs.Order')
            ->get([
                'wfs.Id as StageId',
                'wfs.Order',
                'wfs.StageName',
                'wfs.EscalationLimit',
                'wft.TypeID as WorkFlowTypeID',
                'wfs.Count',
                'p.name as PermissionName',
                'cd.CodeID as StatusCodeID',
                'cd.Value as StatusValue'
            ])
            ->map(function($stage) {
                // Get limits for this stage
                $limits = DB::table('t_WorkFlowLimits as wfl')
                    ->leftJoin('t_Permissions as p', 'wfl.PermissionId', '=', 'p.id')
                    ->where('wfl.WorkFlowStageId', $stage->StageId)
                    ->whereNull('wfl.DeletedOn')
                    ->get(['wfl.MaxAmount', 'p.name as PermissionName'])
                    ->map(function($limit) {
                        return [
                            'MaxAmount' => $limit->MaxAmount,
                            'PermissionName' => $limit->PermissionName,
                        ];
                    })
                    ->toArray();
                
                return [
                    'Order' => $stage->Order,
                    'StageName' => $stage->StageName,
                    'EscalationLimit' => $stage->EscalationLimit,
                    'WorkFlowTypeID' => $stage->WorkFlowTypeID,
                    'Count' => $stage->Count,
                    'PermissionName' => $stage->PermissionName,
                    'StatusCodeID' => $stage->StatusCodeID,
                    'StatusValue' => $stage->StatusValue,
                    'Limits' => $limits,
                ];
            })
            ->toArray();

        // Note: Limits are now part of stages, not separate

        $workflowData[] = [
            'Name' => $workflow->Name,
            'Source' => $workflow->Source,
            'FinalStage' => $workflow->FinalStage,
            'Description' => $workflow->Description,
            'Stages' => $stages,
        ];
        
        echo "    ✓ Exported " . count($stages) . " stages\n";
    }

    $export['data']['workflows'] = $workflowData;
    echo "  ✓ Total workflows exported: " . count($workflowData) . "\n";

    // 3. Export Approval-related Code Details
    echo "\nExporting approval code details...\n";
    $codeDetails = DB::table('t_CodeDetails')
        ->where('CodeID', 'LIKE', '%Approval%')
        ->orWhere('CodeID', 'LIKE', '%WorkFlow%')
        ->orWhere('CodeID', 'IN', ['ApprovalStatus', 'WorkflowStatus'])
        ->whereNull('DeletedOn')
        ->get(['CodeID', 'Description', 'Value', 'DisplayOrder', 'IsActive'])
        ->toArray();
    
    $export['data']['code_details'] = $codeDetails;
    echo "  ✓ Exported " . count($codeDetails) . " code details\n";

    // Write to file
    echo "\nWriting to file...\n";
    $json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($outputFile, $json);

    echo "\n✅ Export completed successfully!\n";
    echo "File: {$outputFile}\n";
    echo "Size: " . number_format(filesize($outputFile) / 1024, 2) . " KB\n\n";

    // Summary
    echo "Summary:\n";
    echo "  - Workflow Types: " . count($export['data']['workflow_types']) . "\n";
    echo "  - Workflows: " . count($export['data']['workflows']) . "\n";
    echo "  - Code Details: " . count($export['data']['code_details']) . "\n";
    echo "\nYou can now import this file into another database using:\n";
    echo "  php scripts/import_workflows.php {$outputFile}\n";

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
