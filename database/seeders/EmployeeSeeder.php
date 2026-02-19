<?php

namespace Database\Seeders;

use App\Enums\Employee\GenderEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\ModelRole;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HR\Employee;
use App\Models\HRM\Department;
use App\Services\BR\BREncryption;
use App\Services\HR\EmployeeService;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Models\Role;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = 'admin@test.co.ke';

        // Idempotent: skip if user already exists
        if (User::where('Email', $adminEmail)->exists()) {
            $this->command->info('EmployeeSeeder: admin user already exists, skipping.');

            return;
        }

        $actor = SystemHelper::user();

        $department = Department::query()->first();
        if (! $department instanceof Department) {
            throw new RuntimeException('No department found');
        }

        $branch = Branch::query()->first();
        if (! $branch instanceof Branch) {
            throw new RuntimeException('No branch found');
        }

        $role = Role::query()->where('name', 'admin')->first();
        if (! $role instanceof Role) {
            throw new RuntimeException('Admin role not found — run RolePermissionSeeder first');
        }

        // Create the employee using the correct array-based API
        $employeeService = EmployeeService::create([
            'FirstName' => 'Default',
            'LastName' => 'User',
            'Email' => $adminEmail,
            'Phone' => '254700100100',
            'Gender' => GenderEnum::Other,
            'BranchID' => $branch->Id,
            'DepartmentID' => $department->Id,
            'EmploymentDate' => now(),
            'Status' => 'Active',
            'IsActive' => 1,
        ], $actor);

        // Create the linked user account
        $userService = $employeeService->createUserAccount($actor);
        $user = $userService->user;

        // Insert directly into t_ModelRoles (the custom branch-role table).
        // getBranch() in LoginRequest queries t_ModelRoles WHERE model_id=user.Id
        // AND model_type=User::getPrimaryKey() AND BranchId=branch.Id.
        // Spatie's roles() relationship writes to model_has_roles (a DIFFERENT table with no BranchId column).
        ModelRole::create([
            'model_id' => $user->Id,
            'model_type' => User::getPrimaryKey(),
            'role_id' => $role->id,
            'BranchId' => $branch->Id,
        ]);

        // Override the UserID — must refresh() after so hashUser() reads 'CSADM', not the old generated UserID
        $user->update(['UserID' => 'CSADM']);
        $user->refresh(); // ← ensures $user->UserID === 'CSADM' before hashing
        $user->update(['Password' => BREncryption::hashUser($user, '2')]);
    }
}
