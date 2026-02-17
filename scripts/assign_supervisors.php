<?php

/**
 * Assign Supervisors to Employees
 * 
 * This script intelligently assigns supervisors to employees based on:
 * - Department hierarchy
 * - Grade levels (higher grades supervise lower grades)
 * - Organizational structure
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================" . PHP_EOL;
echo "Assign Supervisors to Employees" . PHP_EOL;
echo "==========================================" . PHP_EOL . PHP_EOL;

// Get all employees with their departments and grades
$employees = DB::table('t_HREmployees')
    ->join('t_HRJobGrades', 't_HREmployees.GradeID', '=', 't_HRJobGrades.Id')
    ->join('t_Departments', 't_HREmployees.DepartmentID', '=', 't_Departments.Id')
    ->join('t_HRJobRoles', 't_HREmployees.RoleID', '=', 't_HRJobRoles.Id')
    ->select(
        't_HREmployees.*',
        't_HRJobGrades.Code as GradeCode',
        't_HRJobGrades.Name as GradeName',
        't_Departments.Name as DepartmentName',
        't_HRJobRoles.Name as RoleName'
    )
    ->whereNull('t_HREmployees.DeletedOn')
    ->where('t_HREmployees.IsActive', 1)
    ->orderBy('t_Departments.Name')
    ->orderBy('t_HRJobGrades.Code')
    ->get();

echo "Found " . $employees->count() . " active employees" . PHP_EOL . PHP_EOL;

// Group employees by department
$departments = $employees->groupBy('DepartmentName');

echo "Departments: " . $departments->count() . PHP_EOL;
foreach ($departments as $deptName => $deptEmployees) {
    echo "  - {$deptName}: {$deptEmployees->count()} employees" . PHP_EOL;
}

echo PHP_EOL . "ASSIGNMENT STRATEGY:" . PHP_EOL;
echo "  1. G1 (Executive) employees have no supervisor or report to CEO" . PHP_EOL;
echo "  2. G2 (Senior) employees report to G1 in same department or CEO" . PHP_EOL;
echo "  3. G3 (Middle) employees report to G1 or G2 in same department" . PHP_EOL;
echo "  4. G4-G7 employees report to G3, G2, or G1 in same department" . PHP_EOL;
echo PHP_EOL;

echo "Do you want to continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "Assignment cancelled." . PHP_EOL;
    exit(0);
}

DB::beginTransaction();

try {
    $updated = 0;
    $skipped = 0;
    $assignments = [];

    foreach ($departments as $deptName => $deptEmployees) {
        echo PHP_EOL . "Processing: {$deptName}" . PHP_EOL;
        echo str_repeat("-", 50) . PHP_EOL;

        // Separate employees by grade
        $g1Employees = $deptEmployees->where('GradeCode', 'G1');
        $g2Employees = $deptEmployees->where('GradeCode', 'G2');
        $g3Employees = $deptEmployees->where('GradeCode', 'G3');
        $g4Employees = $deptEmployees->where('GradeCode', 'G4');
        $g5Employees = $deptEmployees->where('GradeCode', 'G5');
        $g6Employees = $deptEmployees->where('GradeCode', 'G6');
        $g7Employees = $deptEmployees->where('GradeCode', 'G7');

        // Find department head (G1 or highest grade available)
        $departmentHead = $g1Employees->first() ?? $g2Employees->first() ?? $g3Employees->first();

        if ($departmentHead) {
            echo "  Department Head: {$departmentHead->FirstName} {$departmentHead->LastName} ({$departmentHead->GradeCode})" . PHP_EOL;
        }

        // Assign supervisors for G2 employees
        foreach ($g2Employees as $employee) {
            $supervisor = $g1Employees->first();
            if ($supervisor && $supervisor->Id !== $employee->Id) {
                DB::table('t_HREmployees')
                    ->where('Id', $employee->Id)
                    ->update([
                        'SupervisorID' => $supervisor->Id,
                        'ModifiedBy' => $employee->CreatedBy,
                        'ModifiedOn' => now(),
                    ]);
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → {$supervisor->FirstName} {$supervisor->LastName} ({$supervisor->GradeCode})" . PHP_EOL;
                $updated++;
                $assignments[] = [
                    'employee' => "{$employee->FirstName} {$employee->LastName}",
                    'supervisor' => "{$supervisor->FirstName} {$supervisor->LastName}",
                    'department' => $deptName
                ];
            } else {
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → No supervisor (Top level)" . PHP_EOL;
                $skipped++;
            }
        }

        // Assign supervisors for G3 employees
        foreach ($g3Employees as $employee) {
            // Prefer G1, fallback to G2
            $supervisor = $g1Employees->first() ?? $g2Employees->first();
            if ($supervisor && $supervisor->Id !== $employee->Id) {
                DB::table('t_HREmployees')
                    ->where('Id', $employee->Id)
                    ->update([
                        'SupervisorID' => $supervisor->Id,
                        'ModifiedBy' => $employee->CreatedBy,
                        'ModifiedOn' => now(),
                    ]);
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → {$supervisor->FirstName} {$supervisor->LastName} ({$supervisor->GradeCode})" . PHP_EOL;
                $updated++;
                $assignments[] = [
                    'employee' => "{$employee->FirstName} {$employee->LastName}",
                    'supervisor' => "{$supervisor->FirstName} {$supervisor->LastName}",
                    'department' => $deptName
                ];
            } else {
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → No supervisor available" . PHP_EOL;
                $skipped++;
            }
        }

        // Assign supervisors for G4 employees
        foreach ($g4Employees as $employee) {
            // Prefer G3, fallback to G2, then G1
            $supervisor = $g3Employees->first() ?? $g2Employees->first() ?? $g1Employees->first();
            if ($supervisor && $supervisor->Id !== $employee->Id) {
                DB::table('t_HREmployees')
                    ->where('Id', $employee->Id)
                    ->update([
                        'SupervisorID' => $supervisor->Id,
                        'ModifiedBy' => $employee->CreatedBy,
                        'ModifiedOn' => now(),
                    ]);
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → {$supervisor->FirstName} {$supervisor->LastName} ({$supervisor->GradeCode})" . PHP_EOL;
                $updated++;
                $assignments[] = [
                    'employee' => "{$employee->FirstName} {$employee->LastName}",
                    'supervisor' => "{$supervisor->FirstName} {$supervisor->LastName}",
                    'department' => $deptName
                ];
            } else {
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → No supervisor available" . PHP_EOL;
                $skipped++;
            }
        }

        // Assign supervisors for G5 employees
        foreach ($g5Employees as $employee) {
            // Prefer G4, fallback to G3, G2, then G1
            $supervisor = $g4Employees->first() ?? $g3Employees->first() ?? $g2Employees->first() ?? $g1Employees->first();
            if ($supervisor && $supervisor->Id !== $employee->Id) {
                DB::table('t_HREmployees')
                    ->where('Id', $employee->Id)
                    ->update([
                        'SupervisorID' => $supervisor->Id,
                        'ModifiedBy' => $employee->CreatedBy,
                        'ModifiedOn' => now(),
                    ]);
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → {$supervisor->FirstName} {$supervisor->LastName} ({$supervisor->GradeCode})" . PHP_EOL;
                $updated++;
                $assignments[] = [
                    'employee' => "{$employee->FirstName} {$employee->LastName}",
                    'supervisor' => "{$supervisor->FirstName} {$supervisor->LastName}",
                    'department' => $deptName
                ];
            } else {
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → No supervisor available" . PHP_EOL;
                $skipped++;
            }
        }

        // Assign supervisors for G6 employees
        foreach ($g6Employees as $employee) {
            // Prefer G5, fallback to G4, G3, G2, then G1
            $supervisor = $g5Employees->first() ?? $g4Employees->first() ?? $g3Employees->first() ?? $g2Employees->first() ?? $g1Employees->first();
            if ($supervisor && $supervisor->Id !== $employee->Id) {
                DB::table('t_HREmployees')
                    ->where('Id', $employee->Id)
                    ->update([
                        'SupervisorID' => $supervisor->Id,
                        'ModifiedBy' => $employee->CreatedBy,
                        'ModifiedOn' => now(),
                    ]);
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → {$supervisor->FirstName} {$supervisor->LastName} ({$supervisor->GradeCode})" . PHP_EOL;
                $updated++;
                $assignments[] = [
                    'employee' => "{$employee->FirstName} {$employee->LastName}",
                    'supervisor' => "{$supervisor->FirstName} {$supervisor->LastName}",
                    'department' => $deptName
                ];
            } else {
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → No supervisor available" . PHP_EOL;
                $skipped++;
            }
        }

        // Assign supervisors for G7 employees
        foreach ($g7Employees as $employee) {
            // Prefer G6, fallback up the chain
            $supervisor = $g6Employees->first() ?? $g5Employees->first() ?? $g4Employees->first() ?? $g3Employees->first() ?? $g2Employees->first() ?? $g1Employees->first();
            if ($supervisor && $supervisor->Id !== $employee->Id) {
                DB::table('t_HREmployees')
                    ->where('Id', $employee->Id)
                    ->update([
                        'SupervisorID' => $supervisor->Id,
                        'ModifiedBy' => $employee->CreatedBy,
                        'ModifiedOn' => now(),
                    ]);
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → {$supervisor->FirstName} {$supervisor->LastName} ({$supervisor->GradeCode})" . PHP_EOL;
                $updated++;
                $assignments[] = [
                    'employee' => "{$employee->FirstName} {$employee->LastName}",
                    'supervisor' => "{$supervisor->FirstName} {$supervisor->LastName}",
                    'department' => $deptName
                ];
            } else {
                echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → No supervisor available" . PHP_EOL;
                $skipped++;
            }
        }

        // G1 employees typically don't have supervisors (or report to CEO)
        foreach ($g1Employees as $employee) {
            echo "    {$employee->FirstName} {$employee->LastName} ({$employee->GradeCode}) → No supervisor (Executive level)" . PHP_EOL;
            $skipped++;
        }
    }

    echo PHP_EOL . "==========================================" . PHP_EOL;
    echo "Summary:" . PHP_EOL;
    echo "  - Employees with supervisors assigned: {$updated}" . PHP_EOL;
    echo "  - Employees without supervisors: {$skipped}" . PHP_EOL;
    echo "==========================================" . PHP_EOL . PHP_EOL;

    // Show supervisor summary
    echo "SUPERVISOR SUMMARY:" . PHP_EOL;
    $supervisorStats = DB::table('t_HREmployees as e')
        ->join('t_HREmployees as s', 'e.SupervisorID', '=', 's.Id')
        ->select(
            's.Id',
            's.EmployeeNo',
            's.FirstName',
            's.LastName',
            DB::raw('COUNT(*) as direct_reports')
        )
        ->groupBy('s.Id', 's.EmployeeNo', 's.FirstName', 's.LastName')
        ->orderBy('direct_reports', 'desc')
        ->get();

    foreach ($supervisorStats as $stat) {
        echo sprintf(
            "  %s - %s %s: %d direct reports" . PHP_EOL,
            $stat->EmployeeNo,
            $stat->FirstName,
            $stat->LastName,
            $stat->direct_reports
        );
    }

    echo PHP_EOL . "Do you want to commit these changes? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);

    if (strtolower($line) === 'yes') {
        DB::commit();
        echo PHP_EOL . "✓ Supervisors assigned successfully!" . PHP_EOL;
        echo PHP_EOL . "NEXT STEPS:" . PHP_EOL;
        echo "1. Review supervisor assignments in HR system" . PHP_EOL;
        echo "2. Manually adjust where needed (e.g., specific team structures)" . PHP_EOL;
        echo "3. Set up leave approval workflows based on supervisors" . PHP_EOL;
        echo "4. Configure performance appraisal chains" . PHP_EOL;
        echo "5. Set up timesheet approval workflows" . PHP_EOL;
        echo "6. Review organizational chart" . PHP_EOL;
    } else {
        DB::rollBack();
        echo PHP_EOL . "Changes rolled back. No data was saved." . PHP_EOL;
    }

} catch (Exception $e) {
    DB::rollBack();
    echo PHP_EOL . "ERROR: Failed to assign supervisors!" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "Done." . PHP_EOL;
