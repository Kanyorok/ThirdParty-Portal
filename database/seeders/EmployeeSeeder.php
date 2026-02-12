<?php

namespace Database\Seeders;

use App\Enums\Employee\GenderEnum;
use App\Helpers\SystemHelper;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Services\BR\BREncryption;
use App\Services\HR\EmployeeService;
use App\Services\HRM\UserService;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Models\Role;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            $actor = SystemHelper::user();
            $department = Department::query()->first();
            if (!$department instanceof Department) {
                throw new RuntimeException('No department found');
            }
            $branch = Branch::query()->first();
            if (!$branch instanceof Branch) {
                throw new RuntimeException('No branch found');
            }
        $role = Role::query()->latest('id')->first();
        if (!$role instanceof Role) {
            throw new RuntimeException('No role found');
        }

        // Create employee using the new HR service
        $employeeService = EmployeeService::create([
            'FirstName' => 'Default',
            'LastName' => 'User',
            'Email' => 'admin@test.co.ke',
            'Phone' => '+254700100100',
            'DepartmentID' => $department->Id,
            'BranchID' => $branch->Id,
            'EmploymentDate' => now(),
            'Gender' => 'Other',
            'Status' => 'Active',
            'IsActive' => 1,
        ], $actor);

        // Create user account for the employee
        $hrUserService = $employeeService->createUserAccount($actor);
        $user = $hrUserService->user;

        // Assign role to user at the branch (required for login)
        $hrmUserService = new UserService($user);
        $hrmUserService->setRole($role, $branch, $actor);

        // Update UserID first, then hash password with the correct UserID
        $user->update(['UserID' => 'CSADM']);
        $user->refresh();
        $user->update(['Password' => BREncryption::hashUser($user, '2')]);


    }
}
