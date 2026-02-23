<?php

/**
 * Enhanced Employee Migration Script with Dummy Data
 * 
 * Transfers all employees from t_Employees to t_HREmployees
 * with realistic dummy data for new HR fields
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "==========================================" . PHP_EOL;
echo "Enhanced Employee Migration with Dummy Data" . PHP_EOL;
echo "From: t_Employees → To: t_HREmployees" . PHP_EOL;
echo "==========================================" . PHP_EOL . PHP_EOL;

// Check tables exist
$tablesExist = DB::select("
    SELECT COUNT(*) as count 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_NAME IN ('t_Employees', 't_HREmployees')
");

if ($tablesExist[0]->count < 2) {
    echo "ERROR: One or both tables do not exist!" . PHP_EOL;
    exit(1);
}

// Get count
$sourceCount = DB::table('t_Employees')->whereNull('DeletedOn')->count();
echo "Found {$sourceCount} active employees in t_Employees" . PHP_EOL . PHP_EOL;

if ($sourceCount === 0) {
    echo "No employees to migrate. Exiting." . PHP_EOL;
    exit(0);
}

// Get reference data for dummy values
echo "Loading reference data..." . PHP_EOL;

$branches = DB::table('t_Branches')->get();
$departments = DB::table('t_Departments')->get();
$grades = DB::table('t_HRJobGrades')->get();
$roles = DB::table('t_HRJobRoles')->get();
$banks = DB::table('t_Banks')->get();
$bankBranches = DB::table('t_BankBranches')->get();
$religions = ['Christianity', 'Islam', 'Hinduism', 'Buddhism', 'Other'];

echo "  - Branches: " . $branches->count() . PHP_EOL;
echo "  - Departments: " . $departments->count() . PHP_EOL;
echo "  - Grades: " . $grades->count() . PHP_EOL;
echo "  - Roles: " . $roles->count() . PHP_EOL;
echo "  - Banks: " . $banks->count() . PHP_EOL;
echo "  - Bank Branches: " . $bankBranches->count() . PHP_EOL;

// Dummy data arrays
$employmentTypes = ['Permanent', 'Contract', 'Temporary', 'Internship', 'Part-Time'];
$contractTypes = ['Full-Time', 'Part-Time', 'Fixed-Term', 'Permanent', 'Temporary'];
$paymentModes = ['Bank', 'Bank', 'Cash', 'Mobile']; // More weighted towards Bank
$maritalStatusMap = [
    'S' => 'Single',
    'M' => 'Married',
    'D' => 'Divorced',
    'W' => 'Widowed'
];

echo PHP_EOL . "This will migrate {$sourceCount} employees with enhanced dummy data." . PHP_EOL;
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
    $oldEmployees = DB::table('t_Employees')->whereNull('DeletedOn')->get();
    $migratedCount = 0;
    $skippedCount = 0;
    $errors = [];
    $employeeNoCounter = DB::table('t_HREmployees')->count() + 1;

    foreach ($oldEmployees as $index => $oldEmp) {
        echo sprintf("[%d/%d] Processing: %s %s (%s)...", 
            $index + 1, 
            $sourceCount, 
            $oldEmp->FirstName, 
            $oldEmp->LastName, 
            $oldEmp->EmployeeID
        );

        // Check if already exists by Email
        $existingEmployee = DB::table('t_HREmployees')
            ->where('Email', $oldEmp->Email)
            ->first();
        
        if ($existingEmployee) {
            echo " SKIPPED (already exists)" . PHP_EOL;
            $skippedCount++;
            continue;
        }

        try {
            // Generate new EmployeeNo
            $employeeNo = 'E' . str_pad($employeeNoCounter, 5, '0', STR_PAD_LEFT);
            $employeeNoCounter++;

            // Get or assign branch
            $branch = $branches->firstWhere('Id', $oldEmp->BranchId) 
                ?? $branches->first();
            
            // Get or assign department
            $department = $departments->firstWhere('Id', $oldEmp->DepartmentId) 
                ?? $departments->first();
            
            // Randomly assign grade (if available)
            $grade = $grades->count() > 0 ? $grades->random() : null;
            
            // Randomly assign role from same grade or any role (if available)
            $role = null;
            if ($roles->count() > 0) {
                if ($grade && $roles->where('GradeID', $grade->Id)->count() > 0) {
                    $role = $roles->where('GradeID', $grade->Id)->random();
                } else {
                    $role = $roles->random();
                }
            }

            // Generate realistic employment data
            $employmentDate = $oldEmp->JoinDate 
                ?? now()->subYears(rand(1, 10))->format('Y-m-d');
            
            $employmentType = $employmentTypes[array_rand($employmentTypes)];
            $contractType = $contractTypes[array_rand($contractTypes)];

            // Generate statutory numbers
            $nssfNo = 'NSSF' . rand(100000000, 999999999);
            $nhifNo = 'NHIF' . rand(100000000, 999999999);
            $kraPin = 'A' . rand(100000000, 999999999) . strtoupper(substr($oldEmp->LastName, 0, 1));

            // Generate salary (between KSh 20,000 and KSh 250,000)
            $basicSalary = rand(20000, 250000);
            // Round to nearest 1000
            $basicSalary = round($basicSalary / 1000) * 1000;

            // Bank details
            $paymentMode = $paymentModes[array_rand($paymentModes)];
            $bank = null;
            $bankBranch = null;
            $bankAccount = null;

            if ($paymentMode === 'Bank' && $banks->count() > 0) {
                $bank = $banks->random();
                $bankBranchesForBank = $bankBranches->where('BankID', $bank->BankID);
                if ($bankBranchesForBank->count() > 0) {
                    $bankBranch = $bankBranchesForBank->random();
                } else {
                    $bankBranch = $bankBranches->count() > 0 ? $bankBranches->random() : null;
                }
                $bankAccount = rand(1000000000, 9999999999);
            }

            // Religion
            $religion = $religions[array_rand($religions)];

            // Gender normalization
            $gender = strtoupper($oldEmp->Gender ?? 'O');
            if (!in_array($gender, ['M', 'F', 'O'])) {
                $gender = ['Male' => 'M', 'Female' => 'F', 'Other' => 'O'][trim($oldEmp->Gender)] ?? 'O';
            }
            $genderFull = ['M' => 'Male', 'F' => 'Female', 'O' => 'Other'][$gender];

            // Date of Birth (ensure 20+ years old)
            $dob = $oldEmp->DateOfBirth 
                ?? now()->subYears(rand(25, 60))->format('Y-m-d');

            // Prepare insert data
            $newEmployeeData = [
                'EmployeeNo' => $employeeNo,
                'FirstName' => $oldEmp->FirstName,
                'LastName' => $oldEmp->LastName,
                'OtherNames' => $oldEmp->MiddleName,
                'Email' => $oldEmp->Email,
                'Phone' => $oldEmp->Phone,
                'Gender' => $genderFull,
                'Religion' => $religion,
                'DateOfBirth' => $dob,
                'Address' => $oldEmp->Address ?? 'P.O. Box ' . rand(1000, 99999) . ', Nairobi, Kenya',
                
                'BranchID' => $branch->Id,
                'DepartmentID' => $department->Id,
                'GradeID' => $grade ? $grade->Id : null,
                'RoleID' => $role ? $role->Id : null,
                'SupervisorID' => null, // Will assign later
                
                'EmploymentDate' => $employmentDate,
                'EmploymentType' => $employmentType,
                'ContractType' => $contractType,
                
                'NSSFNo' => $nssfNo,
                'NHIFNo' => $nhifNo,
                'KRAPIN' => $kraPin,
                
                'BasicSalary' => $basicSalary,
                'PaymentMode' => $paymentMode,
                'BankID' => $bank ? $bank->BankID : null,
                'BankBranchID' => $bankBranch ? $bankBranch->BranchID : null,
                'BankAccount' => $bankAccount,
                
                'Status' => 'Active',
                'StatusReason' => 'Migrated from legacy system',
                'StatusChangedOn' => now(),
                'StatusChangedBy' => $oldEmp->CreatedBy,
                'IsActive' => 1,
                
                'PhotoPath' => null, // Will link photos later if needed
                
                'CreatedBy' => $oldEmp->CreatedBy,
                'CreatedOn' => $oldEmp->CreatedOn ?? now(),
                'ModifiedBy' => $oldEmp->ModifiedBy ?? $oldEmp->CreatedBy,
                'ModifiedOn' => $oldEmp->ModifiedOn ?? now(),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ];

            // Insert into new table
            $newId = DB::table('t_HREmployees')->insertGetId($newEmployeeData);

            // Update t_Users if exists
            DB::table('t_Users')
                ->where('EmployeeId', $oldEmp->Id)
                ->update(['EmployeeId' => $newId]);

            echo " ✓ MIGRATED (New ID: {$newId}, Salary: KSh " . number_format($basicSalary, 2) . ")" . PHP_EOL;
            $migratedCount++;

        } catch (Exception $e) {
            echo " ✗ ERROR" . PHP_EOL;
            $errors[] = [
                'employee' => "{$oldEmp->FirstName} {$oldEmp->LastName}",
                'error' => $e->getMessage()
            ];
        }
    }

    echo PHP_EOL . "==========================================" . PHP_EOL;
    echo "Migration Summary:" . PHP_EOL;
    echo "  - Migrated: {$migratedCount}" . PHP_EOL;
    echo "  - Skipped: {$skippedCount}" . PHP_EOL;
    echo "  - Errors: " . count($errors) . PHP_EOL;
    echo "==========================================" . PHP_EOL . PHP_EOL;

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
        echo "1. All employees have been given dummy data for new fields" . PHP_EOL;
        echo "2. Salaries range from KSh 20,000 to KSh 250,000" . PHP_EOL;
        echo "3. NSSF, NHIF, and KRA PIN numbers are generated dummy values" . PHP_EOL;
        echo "4. Bank accounts assigned where payment mode is 'Bank'" . PHP_EOL;
        echo "5. Employment dates preserved from old system where available" . PHP_EOL;
        echo "6. All employees set to 'Active' status" . PHP_EOL;
        echo "7. User accounts have been updated to reference new employee IDs" . PHP_EOL;
        echo PHP_EOL;
        echo "NEXT STEPS:" . PHP_EOL;
        echo "1. Review migrated data in HR system" . PHP_EOL;
        echo "2. Update supervisor relationships if needed" . PHP_EOL;
        echo "3. Verify and correct salary information" . PHP_EOL;
        echo "4. Update statutory numbers with actual values" . PHP_EOL;
        echo "5. Verify bank account details" . PHP_EOL;
        echo "6. Add emergency contacts for employees" . PHP_EOL;
        echo "7. Upload employee documents" . PHP_EOL;
        echo "8. Set up employee work schedules" . PHP_EOL;
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
