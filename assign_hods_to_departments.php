<?php

/**
 * Assign HODs to Departments
 * 
 * This script assigns Head of Departments based on the top-level
 * supervisors (employees without supervisors) in each department
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================" . PHP_EOL;
echo "Assign HODs to Departments" . PHP_EOL;
echo "==========================================" . PHP_EOL . PHP_EOL;

DB::beginTransaction();

try {
    $departments = DB::table('t_Departments')->whereNull('DeletedOn')->get();
    
    echo "Processing " . $departments->count() . " departments..." . PHP_EOL . PHP_EOL;
    
    $assigned = 0;
    $skipped = 0;
    
    foreach ($departments as $dept) {
        echo "Department: {$dept->Name}" . PHP_EOL;
        
        // Find top-level employees (without supervisors) in this department
        // These are typically the department heads
        $topLevelEmployees = DB::table('t_HREmployees')
            ->join('t_HRJobGrades', 't_HREmployees.GradeID', '=', 't_HRJobGrades.Id')
            ->where('t_HREmployees.DepartmentID', $dept->Id)
            ->whereNull('t_HREmployees.SupervisorID')
            ->whereNull('t_HREmployees.DeletedOn')
            ->where('t_HREmployees.IsActive', 1)
            ->select('t_HREmployees.*', 't_HRJobGrades.Code as GradeCode')
            ->orderBy('t_HRJobGrades.Code')
            ->get();
        
        if ($topLevelEmployees->count() > 0) {
            // Assign the first top-level employee as HOD
            $hod = $topLevelEmployees->first();
            
            // If there are multiple top-level employees, the second one can be deputy
            $deputy = $topLevelEmployees->count() > 1 ? $topLevelEmployees->get(1) : null;
            
            DB::table('t_Departments')
                ->where('Id', $dept->Id)
                ->update([
                    'HeadId' => $hod->Id,
                    'DeputyHeadId' => $deputy ? $deputy->Id : null,
                    'ModifiedOn' => now()
                ]);
            
            echo "  ✓ HOD: {$hod->FirstName} {$hod->LastName} ({$hod->GradeCode})" . PHP_EOL;
            if ($deputy) {
                echo "  ✓ Deputy: {$deputy->FirstName} {$deputy->LastName} ({$deputy->GradeCode})" . PHP_EOL;
            }
            $assigned++;
        } else {
            echo "  ⚠ No top-level employee found - HOD not assigned" . PHP_EOL;
            $skipped++;
        }
        echo PHP_EOL;
    }
    
    echo "==========================================" . PHP_EOL;
    echo "Summary:" . PHP_EOL;
    echo "  - Departments with HOD assigned: {$assigned}" . PHP_EOL;
    echo "  - Departments without HOD: {$skipped}" . PHP_EOL;
    echo "==========================================" . PHP_EOL . PHP_EOL;
    
    echo "Do you want to commit these changes? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) === 'yes') {
        DB::commit();
        echo PHP_EOL . "✓ HODs assigned successfully!" . PHP_EOL;
    } else {
        DB::rollBack();
        echo PHP_EOL . "Changes rolled back. No data was saved." . PHP_EOL;
    }
    
} catch (Exception $e) {
    DB::rollBack();
    echo PHP_EOL . "ERROR: Failed to assign HODs!" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "Done." . PHP_EOL;
