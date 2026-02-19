<?php

namespace Database\Seeders;

use App\Enums\Employee\GenderEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Services\BR\BREncryption;
use App\Services\HR\EmployeeService;
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

        $role = Role::query()->latest('id')->first();
        if (! $role instanceof Role) {
            throw new RuntimeException('No role found');
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

        // Assign role
        $user->assignRole($role);

        // Override the UserID and Password
        $user->refresh();
        $user->update([
            'UserID' => 'CSADM',
        ]);
        $user->update([
            'Password' => BREncryption::hashUser($user, '2'),
        ]);
    }
}
