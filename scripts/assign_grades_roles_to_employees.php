<?php

/**
 * Assign Job Grades and Roles to Existing Employees
 * 
 * This script intelligently assigns appropriate grades and roles
 * to employees based on their department, position, and salary
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================" . PHP_EOL;
echo "Assign Grades and Roles to Employees" . PHP_EOL;
echo "==========================================" . PHP_EOL . PHP_EOL;

// Get all employees
$employees = DB::table('t_HREmployees')
    ->whereNull('DeletedOn')
    ->where('IsActive', 1)
    ->get();

echo "Found " . $employees->count() . " active employees" . PHP_EOL . PHP_EOL;

// Get all grades and roles
$grades = DB::table('t_HRJobGrades')->get()->keyBy('Code');
$roles = DB::table('t_HRJobRoles')->get();
$departments = DB::table('t_Departments')->get()->keyBy('Id');

echo "Available Grades: " . $grades->count() . PHP_EOL;
echo "Available Roles: " . $roles->count() . PHP_EOL . PHP_EOL;

DB::beginTransaction();

try {
    $updated = 0;
    $skipped = 0;

    foreach ($employees as $employee) {
        $department = $departments->get($employee->DepartmentID);
        $departmentName = $department ? $department->Name : 'Unknown';
        
        // Determine grade based on salary
        $grade = null;
        $salary = $employee->BasicSalary;
        
        if ($salary >= 200000) {
            $grade = $grades->get('G1'); // Executive Management
        } elseif ($salary >= 150000) {
            $grade = $grades->get('G2'); // Senior Management
        } elseif ($salary >= 100000) {
            $grade = $grades->get('G3'); // Middle Management
        } elseif ($salary >= 70000) {
            $grade = $grades->get('G4'); // Supervisory Level
        } elseif ($salary >= 50000) {
            $grade = $grades->get('G5'); // Professional Level
        } elseif ($salary >= 25000) {
            $grade = $grades->get('G6'); // Entry Level
        } else {
            $grade = $grades->get('G7'); // Support Staff
        }

        // Find appropriate role based on department and grade
        $role = null;
        $rolesForDept = $roles->where('DepartmentID', $employee->DepartmentID)
                              ->where('GradeID', $grade->Id);
        
        if ($rolesForDept->count() > 0) {
            // Assign specific department role
            $role = $rolesForDept->first();
        } else {
            // Fallback to general roles for the grade
            $generalRoles = $roles->whereNull('DepartmentID')
                                 ->where('GradeID', $grade->Id);
            
            if ($generalRoles->count() > 0) {
                $role = $generalRoles->first();
            } else {
                // Last resort: any role in the same grade
                $anyRole = $roles->where('GradeID', $grade->Id)->first();
                if ($anyRole) {
                    $role = $anyRole;
                }
            }
        }

        if ($grade && $role) {
            DB::table('t_HREmployees')
                ->where('Id', $employee->Id)
                ->update([
                    'GradeID' => $grade->Id,
                    'RoleID' => $role->Id,
                    'ModifiedBy' => $employee->CreatedBy,
                    'ModifiedOn' => now(),
                ]);
            
            echo sprintf(
                "[%d] %s %s - %s | %s (%s) | %s - KSh %s" . PHP_EOL,
                $employee->Id,
                $employee->FirstName,
                $employee->LastName,
                $departmentName,
                $grade->Name,
                $grade->Code,
                $role->Name,
                number_format($employee->BasicSalary)
            );
            $updated++;
        } else {
            echo sprintf(
                "[%d] %s %s - SKIPPED (Could not find grade/role)" . PHP_EOL,
                $employee->Id,
                $employee->FirstName,
                $employee->LastName
            );
            $skipped++;
        }
    }

    echo PHP_EOL . "==========================================" . PHP_EOL;
    echo "Summary:" . PHP_EOL;
    echo "  - Employees Updated: {$updated}" . PHP_EOL;
    echo "  - Employees Skipped: {$skipped}" . PHP_EOL;
    echo "==========================================" . PHP_EOL . PHP_EOL;

    // Show distribution by grade
    echo "Distribution by Grade:" . PHP_EOL;
    $gradeDistribution = DB::table('t_HREmployees')
        ->join('t_HRJobGrades', 't_HREmployees.GradeID', '=', 't_HRJobGrades.Id')
        ->select('t_HRJobGrades.Name', 't_HRJobGrades.Code', DB::raw('COUNT(*) as count'))
        ->groupBy('t_HRJobGrades.Name', 't_HRJobGrades.Code')
        ->orderBy('t_HRJobGrades.Code')
        ->get();
    
    foreach ($gradeDistribution as $dist) {
        echo sprintf("  %-30s (%s): %d employees" . PHP_EOL, $dist->Name, $dist->Code, $dist->count);
    }

    echo PHP_EOL . "Do you want to commit these changes? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);

    if (strtolower($line) === 'yes') {
        DB::commit();
        echo PHP_EOL . "✓ Grades and roles assigned successfully!" . PHP_EOL;
        echo PHP_EOL . "NEXT STEPS:" . PHP_EOL;
        echo "1. Review employee grades and roles in the HR system" . PHP_EOL;
        echo "2. Manually adjust any misassigned roles" . PHP_EOL;
        echo "3. Set up supervisor relationships" . PHP_EOL;
        echo "4. Configure leave entitlements per grade" . PHP_EOL;
        echo "5. Set up overtime rates per grade" . PHP_EOL;
    } else {
        DB::rollBack();
        echo PHP_EOL . "Changes rolled back. No data was saved." . PHP_EOL;
    }

} catch (Exception $e) {
    DB::rollBack();
    echo PHP_EOL . "ERROR: Failed to assign grades and roles!" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "Done." . PHP_EOL;
