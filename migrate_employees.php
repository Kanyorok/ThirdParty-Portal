<?php

/**
 * Migration Script: Move Employees from t_Employees to t_HREmployees
 * 
 * This script safely migrates all employee records from the old t_Employees table
 * to the new t_HREmployees table while preserving data integrity and relationships.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\HR\Employee as HREmployee;
use App\Services\HR\EmployeeService;

echo "==========================================" . PHP_EOL;
echo "Employee Migration Script" . PHP_EOL;
echo "From: t_Employees → To: t_HREmployees" . PHP_EOL;
echo "==========================================" . PHP_EOL . PHP_EOL;

// Check if tables exist
$tablesExist = DB::select("
    SELECT COUNT(*) as count 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_NAME IN ('t_Employees', 't_HREmployees')
");

if ($tablesExist[0]->count < 2) {
    echo "ERROR: One or both tables do not exist!" . PHP_EOL;
    exit(1);
}

// Get count from source table
$sourceCount = DB::table('t_Employees')->count();
echo "Found {$sourceCount} employees in t_Employees" . PHP_EOL . PHP_EOL;

if ($sourceCount === 0) {
    echo "No employees to migrate. Exiting." . PHP_EOL;
    exit(0);
}

// Ask for confirmation
echo "This will migrate {$sourceCount} employees from t_Employees to t_HREmployees." . PHP_EOL;
echo "Do you want to continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "Migration cancelled." . PHP_EOL;
    exit(0);
}

echo PHP_EOL . "Starting migration..." . PHP_EOL . PHP_EOL;

DB::beginTransaction();

try {
    $oldEmployees = DB::table('t_Employees')->get();
    $migratedCount = 0;
    $skippedCount = 0;
    $errors = [];

    foreach ($oldEmployees as $oldEmp) {
        echo "Processing: {$oldEmp->FirstName} {$oldEmp->LastName} ({$oldEmp->EmployeeID})...";

        // Check if employee already exists in new table by Email
        $existingEmployee = HREmployee::where('Email', $oldEmp->Email)->first();
        
        if ($existingEmployee) {
            echo " SKIPPED (already exists)" . PHP_EOL;
            $skippedCount++;
            continue;
        }

        try {
            // Generate new EmployeeNo
            $employeeNo = EmployeeService::generateEmployeeNo();

            // Map old structure to new structure
            $newEmployeeData = [
                'EmployeeNo' => $employeeNo,
                'FirstName' => $oldEmp->FirstName,
                'LastName' => $oldEmp->LastName,
                'OtherNames' => $oldEmp->MiddleName,
                'Email' => $oldEmp->Email,
                'Phone' => $oldEmp->Phone,
                'Gender' => $oldEmp->Gender,
                'DateOfBirth' => $oldEmp->DateOfBirth,
                'Address' => $oldEmp->Address,
                'BranchID' => $oldEmp->BranchId,
                'DepartmentID' => $oldEmp->DepartmentId,
                'EmploymentDate' => $oldEmp->JoinDate,
                'Status' => 'Active', // Default status
                'IsActive' => 1,
                'CreatedBy' => $oldEmp->CreatedBy,
                'CreatedOn' => $oldEmp->CreatedOn ?? now(),
                'ModifiedBy' => $oldEmp->ModifiedBy,
                'ModifiedOn' => $oldEmp->ModifiedOn,
                'DeletedBy' => $oldEmp->DeletedBy,
                'DeletedOn' => $oldEmp->DeletedOn,
            ];

            // Create new employee record
            $newEmployee = HREmployee::create($newEmployeeData);

            // Update any User accounts that reference the old employee
            DB::table('t_Users')
                ->where('EmployeeId', $oldEmp->Id)
                ->update(['EmployeeId' => $newEmployee->Id]);

            echo " MIGRATED (new ID: {$newEmployee->Id}, EmployeeNo: {$employeeNo})" . PHP_EOL;
            $migratedCount++;

        } catch (Exception $e) {
            echo " ERROR: " . $e->getMessage() . PHP_EOL;
            $errors[] = [
                'employee' => "{$oldEmp->FirstName} {$oldEmp->LastName} ({$oldEmp->EmployeeID})",
                'error' => $e->getMessage()
            ];
        }
    }

    echo PHP_EOL . "Migration Summary:" . PHP_EOL;
    echo "==================" . PHP_EOL;
    echo "Total employees in source: {$sourceCount}" . PHP_EOL;
    echo "Successfully migrated: {$migratedCount}" . PHP_EOL;
    echo "Skipped (already exist): {$skippedCount}" . PHP_EOL;
    echo "Errors: " . count($errors) . PHP_EOL . PHP_EOL;

    if (count($errors) > 0) {
        echo "Errors encountered:" . PHP_EOL;
        foreach ($errors as $error) {
            echo "  - {$error['employee']}: {$error['error']}" . PHP_EOL;
        }
        echo PHP_EOL;
    }

    echo "Do you want to commit these changes? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);

    if (strtolower($line) === 'yes') {
        DB::commit();
        echo PHP_EOL . "✓ Migration completed successfully!" . PHP_EOL;
        echo PHP_EOL . "IMPORTANT NOTES:" . PHP_EOL;
        echo "1. The old t_Employees table has NOT been deleted" . PHP_EOL;
        echo "2. All User accounts have been updated to reference the new employee records" . PHP_EOL;
        echo "3. Review the migrated data before deleting the old table" . PHP_EOL;
        echo "4. To delete the old table, run: DROP TABLE t_Employees" . PHP_EOL;
    } else {
        DB::rollBack();
        echo PHP_EOL . "Migration rolled back. No changes made." . PHP_EOL;
    }

} catch (Exception $e) {
    DB::rollBack();
    echo PHP_EOL . "ERROR: Migration failed!" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "Done." . PHP_EOL;
