<?php

/**
 * Job Grades and Roles Seeder Script
 * 
 * This script creates realistic job grades and roles for the organization
 * based on Kenyan organizational structures
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================" . PHP_EOL;
echo "Job Grades and Roles Seeder" . PHP_EOL;
echo "==========================================" . PHP_EOL . PHP_EOL;

// Get departments for role assignment
$departments = DB::table('t_Departments')->get()->keyBy('Name');
echo "Found " . $departments->count() . " departments" . PHP_EOL . PHP_EOL;

// Get a default user for CreatedBy
$defaultUser = DB::table('t_Users')->first();
$createdBy = $defaultUser->Id ?? 1;

DB::beginTransaction();

try {
    // Define Job Grades (typical organizational hierarchy)
    $jobGrades = [
        [
            'Code' => 'G1',
            'Name' => 'Executive Management',
            'MinSalary' => 200000.00,
            'MaxSalary' => 500000.00,
            'Description' => 'Chief Executive Officer, Chief Operating Officer, Managing Director',
        ],
        [
            'Code' => 'G2',
            'Name' => 'Senior Management',
            'MinSalary' => 150000.00,
            'MaxSalary' => 250000.00,
            'Description' => 'Department Heads, Senior Managers, Directors',
        ],
        [
            'Code' => 'G3',
            'Name' => 'Middle Management',
            'MinSalary' => 100000.00,
            'MaxSalary' => 180000.00,
            'Description' => 'Managers, Assistant Managers, Supervisors',
        ],
        [
            'Code' => 'G4',
            'Name' => 'Supervisory Level',
            'MinSalary' => 70000.00,
            'MaxSalary' => 120000.00,
            'Description' => 'Team Leaders, Senior Officers, Coordinators',
        ],
        [
            'Code' => 'G5',
            'Name' => 'Professional Level',
            'MinSalary' => 50000.00,
            'MaxSalary' => 90000.00,
            'Description' => 'Officers, Specialists, Analysts',
        ],
        [
            'Code' => 'G6',
            'Name' => 'Entry Level',
            'MinSalary' => 25000.00,
            'MaxSalary' => 60000.00,
            'Description' => 'Junior Officers, Assistants, Interns',
        ],
        [
            'Code' => 'G7',
            'Name' => 'Support Staff',
            'MinSalary' => 20000.00,
            'MaxSalary' => 40000.00,
            'Description' => 'Administrative Assistants, Clerks, Support Staff',
        ],
    ];

    echo "Creating Job Grades..." . PHP_EOL;
    $gradeIds = [];
    
    foreach ($jobGrades as $grade) {
        $existingGrade = DB::table('t_HRJobGrades')
            ->where('Code', $grade['Code'])
            ->first();
        
        if ($existingGrade) {
            echo "  - {$grade['Name']} ({$grade['Code']}) - Already exists" . PHP_EOL;
            $gradeIds[$grade['Code']] = $existingGrade->Id;
        } else {
            $gradeId = DB::table('t_HRJobGrades')->insertGetId([
                'Code' => $grade['Code'],
                'Name' => $grade['Name'],
                'MinSalary' => $grade['MinSalary'],
                'MaxSalary' => $grade['MaxSalary'],
                'Description' => $grade['Description'],
                'IsActive' => 1,
                'CreatedBy' => $createdBy,
                'CreatedOn' => now(),
            ]);
            $gradeIds[$grade['Code']] = $gradeId;
            echo "  - {$grade['Name']} ({$grade['Code']}) - Created (KSh " . 
                number_format($grade['MinSalary']) . " - KSh " . number_format($grade['MaxSalary']) . ")" . PHP_EOL;
        }
    }

    echo PHP_EOL . "Creating Job Roles..." . PHP_EOL;

    // Define Job Roles by Department
    $jobRoles = [
        // Marketing Department
        [
            'Code' => 'MKT-001',
            'Name' => 'Chief Marketing Officer',
            'GradeCode' => 'G1',
            'DepartmentName' => 'Marketing',
            'Description' => 'Head of Marketing Department',
        ],
        [
            'Code' => 'MKT-002',
            'Name' => 'Marketing Manager',
            'GradeCode' => 'G3',
            'DepartmentName' => 'Marketing',
            'Description' => 'Manages marketing team and strategies',
        ],
        [
            'Code' => 'MKT-003',
            'Name' => 'Marketing Officer',
            'GradeCode' => 'G5',
            'DepartmentName' => 'Marketing',
            'Description' => 'Executes marketing campaigns',
        ],
        [
            'Code' => 'MKT-004',
            'Name' => 'Digital Marketing Specialist',
            'GradeCode' => 'G5',
            'DepartmentName' => 'Marketing',
            'Description' => 'Manages digital marketing and social media',
        ],
        [
            'Code' => 'MKT-005',
            'Name' => 'Marketing Assistant',
            'GradeCode' => 'G6',
            'DepartmentName' => 'Marketing',
            'Description' => 'Supports marketing activities',
        ],

        // Procurement Department
        [
            'Code' => 'PRO-001',
            'Name' => 'Chief Procurement Officer',
            'GradeCode' => 'G1',
            'DepartmentName' => 'Procurement',
            'Description' => 'Head of Procurement Department',
        ],
        [
            'Code' => 'PRO-002',
            'Name' => 'Procurement Manager',
            'GradeCode' => 'G3',
            'DepartmentName' => 'Procurement',
            'Description' => 'Manages procurement processes',
        ],
        [
            'Code' => 'PRO-003',
            'Name' => 'Procurement Officer',
            'GradeCode' => 'G5',
            'DepartmentName' => 'Procurement',
            'Description' => 'Handles procurement activities',
        ],
        [
            'Code' => 'PRO-004',
            'Name' => 'Supplier Relationship Manager',
            'GradeCode' => 'G4',
            'DepartmentName' => 'Procurement',
            'Description' => 'Manages supplier relationships',
        ],
        [
            'Code' => 'PRO-005',
            'Name' => 'Procurement Assistant',
            'GradeCode' => 'G6',
            'DepartmentName' => 'Procurement',
            'Description' => 'Assists with procurement documentation',
        ],

        // Finance Department
        [
            'Code' => 'FIN-001',
            'Name' => 'Chief Financial Officer',
            'GradeCode' => 'G1',
            'DepartmentName' => 'Finance',
            'Description' => 'Head of Finance Department',
        ],
        [
            'Code' => 'FIN-002',
            'Name' => 'Finance Manager',
            'GradeCode' => 'G3',
            'DepartmentName' => 'Finance',
            'Description' => 'Manages financial operations',
        ],
        [
            'Code' => 'FIN-003',
            'Name' => 'Accountant',
            'GradeCode' => 'G5',
            'DepartmentName' => 'Finance',
            'Description' => 'Manages accounts and financial records',
        ],
        [
            'Code' => 'FIN-004',
            'Name' => 'Financial Analyst',
            'GradeCode' => 'G5',
            'DepartmentName' => 'Finance',
            'Description' => 'Analyzes financial data and trends',
        ],
        [
            'Code' => 'FIN-005',
            'Name' => 'Accounts Clerk',
            'GradeCode' => 'G7',
            'DepartmentName' => 'Finance',
            'Description' => 'Handles accounting transactions',
        ],

        // Human Resource Department
        [
            'Code' => 'HR-001',
            'Name' => 'Chief Human Resource Officer',
            'GradeCode' => 'G1',
            'DepartmentName' => 'Human Resource',
            'Description' => 'Head of HR Department',
        ],
        [
            'Code' => 'HR-002',
            'Name' => 'HR Manager',
            'GradeCode' => 'G3',
            'DepartmentName' => 'Human Resource',
            'Description' => 'Manages HR functions',
        ],
        [
            'Code' => 'HR-003',
            'Name' => 'HR Officer',
            'GradeCode' => 'G5',
            'DepartmentName' => 'Human Resource',
            'Description' => 'Handles recruitment and employee relations',
        ],
        [
            'Code' => 'HR-004',
            'Name' => 'Payroll Officer',
            'GradeCode' => 'G5',
            'DepartmentName' => 'Human Resource',
            'Description' => 'Manages payroll processing',
        ],
        [
            'Code' => 'HR-005',
            'Name' => 'HR Assistant',
            'GradeCode' => 'G6',
            'DepartmentName' => 'Human Resource',
            'Description' => 'Supports HR activities',
        ],

        // ICT Support Department
        [
            'Code' => 'ICT-001',
            'Name' => 'Chief Technology Officer',
            'GradeCode' => 'G1',
            'DepartmentName' => 'ICT Support',
            'Description' => 'Head of ICT Department',
        ],
        [
            'Code' => 'ICT-002',
            'Name' => 'IT Manager',
            'GradeCode' => 'G3',
            'DepartmentName' => 'ICT Support',
            'Description' => 'Manages IT infrastructure and team',
        ],
        [
            'Code' => 'ICT-003',
            'Name' => 'System Administrator',
            'GradeCode' => 'G5',
            'DepartmentName' => 'ICT Support',
            'Description' => 'Manages systems and servers',
        ],
        [
            'Code' => 'ICT-004',
            'Name' => 'Software Developer',
            'GradeCode' => 'G5',
            'DepartmentName' => 'ICT Support',
            'Description' => 'Develops and maintains software',
        ],
        [
            'Code' => 'ICT-005',
            'Name' => 'IT Support Specialist',
            'GradeCode' => 'G6',
            'DepartmentName' => 'ICT Support',
            'Description' => 'Provides technical support',
        ],
        [
            'Code' => 'ICT-006',
            'Name' => 'Network Engineer',
            'GradeCode' => 'G5',
            'DepartmentName' => 'ICT Support',
            'Description' => 'Manages network infrastructure',
        ],

        // Research and Development
        [
            'Code' => 'RND-001',
            'Name' => 'Research Director',
            'GradeCode' => 'G2',
            'DepartmentName' => 'Research and Development',
            'Description' => 'Leads research initiatives',
        ],
        [
            'Code' => 'RND-002',
            'Name' => 'Research Officer',
            'GradeCode' => 'G5',
            'DepartmentName' => 'Research and Development',
            'Description' => 'Conducts research activities',
        ],
        [
            'Code' => 'RND-003',
            'Name' => 'Research Assistant',
            'GradeCode' => 'G6',
            'DepartmentName' => 'Research and Development',
            'Description' => 'Supports research projects',
        ],

        // General/Cross-Department Roles
        [
            'Code' => 'GEN-001',
            'Name' => 'Chief Executive Officer',
            'GradeCode' => 'G1',
            'DepartmentName' => null,
            'Description' => 'Head of Organization',
        ],
        [
            'Code' => 'GEN-002',
            'Name' => 'Branch Manager',
            'GradeCode' => 'G3',
            'DepartmentName' => null,
            'Description' => 'Manages branch operations',
        ],
        [
            'Code' => 'GEN-003',
            'Name' => 'Administrative Officer',
            'GradeCode' => 'G5',
            'DepartmentName' => null,
            'Description' => 'Handles administrative tasks',
        ],
        [
            'Code' => 'GEN-004',
            'Name' => 'Receptionist',
            'GradeCode' => 'G7',
            'DepartmentName' => null,
            'Description' => 'Handles reception duties',
        ],
        [
            'Code' => 'GEN-005',
            'Name' => 'Intern',
            'GradeCode' => 'G6',
            'DepartmentName' => null,
            'Description' => 'Internship position',
        ],
    ];

    $rolesCreated = 0;
    $rolesSkipped = 0;

    foreach ($jobRoles as $role) {
        $existingRole = DB::table('t_HRJobRoles')
            ->where('Code', $role['Code'])
            ->first();
        
        if ($existingRole) {
            echo "  - {$role['Name']} ({$role['Code']}) - Already exists" . PHP_EOL;
            $rolesSkipped++;
        } else {
            $departmentId = null;
            if ($role['DepartmentName'] && isset($departments[$role['DepartmentName']])) {
                $departmentId = $departments[$role['DepartmentName']]->Id;
            }

            DB::table('t_HRJobRoles')->insert([
                'Code' => $role['Code'],
                'Name' => $role['Name'],
                'GradeID' => $gradeIds[$role['GradeCode']],
                'DepartmentID' => $departmentId,
                'Description' => $role['Description'],
                'IsActive' => 1,
                'CreatedBy' => $createdBy,
                'CreatedOn' => now(),
            ]);
            
            $dept = $role['DepartmentName'] ?? 'General';
            echo "  - {$role['Name']} ({$role['Code']}) - Created [{$role['GradeCode']} - {$dept}]" . PHP_EOL;
            $rolesCreated++;
        }
    }

    echo PHP_EOL . "==========================================" . PHP_EOL;
    echo "Summary:" . PHP_EOL;
    echo "  - Job Grades Created: " . count($jobGrades) . PHP_EOL;
    echo "  - Job Roles Created: {$rolesCreated}" . PHP_EOL;
    echo "  - Job Roles Skipped: {$rolesSkipped}" . PHP_EOL;
    echo "==========================================" . PHP_EOL . PHP_EOL;

    echo "Do you want to commit these changes? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);

    if (strtolower($line) === 'yes') {
        DB::commit();
        echo PHP_EOL . "✓ Job Grades and Roles created successfully!" . PHP_EOL;
        echo PHP_EOL . "NEXT STEPS:" . PHP_EOL;
        echo "1. You can now assign these grades and roles to employees" . PHP_EOL;
        echo "2. Review and adjust salary ranges if needed" . PHP_EOL;
        echo "3. Create additional roles specific to your organization" . PHP_EOL;
        echo "4. Set up leave types for each grade" . PHP_EOL;
        echo "5. Configure overtime rates for each grade" . PHP_EOL;
    } else {
        DB::rollBack();
        echo PHP_EOL . "Changes rolled back. No data was saved." . PHP_EOL;
    }

} catch (Exception $e) {
    DB::rollBack();
    echo PHP_EOL . "ERROR: Failed to create job grades and roles!" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "Done." . PHP_EOL;
