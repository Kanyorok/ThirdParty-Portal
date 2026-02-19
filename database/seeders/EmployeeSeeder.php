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

        // --- Idempotent cleanup ---
        $existingUser = User::withTrashed()->where('Email', 'admin@test.co.ke')->first();
        if ($existingUser) {
            ModelRole::where('model_id', $existingUser->Id)->delete();
            $existingUser->forceDelete();
        }

        Employee::withTrashed()
            ->where('Email', 'admin@test.co.ke')
            ->get()
            ->each->forceDelete();
        // --- End cleanup ---

        $employeeService = EmployeeService::create([
            'DepartmentId'   => $department->Id,
            'BranchId'       => $branch->Id,
            'FirstName'      => 'Default',
            'LastName'       => 'User',
            'Email'          => 'admin@test.co.ke',
            'Phone'          => '254700100100',
            'EmploymentDate' => now(),
            'Gender'         => GenderEnum::Other->value,
            'Status'         => 'Active',
            'IsActive'       => 1,
        ], $actor);

        $userService = $employeeService->createUserAccount($actor);
        $user = $userService->user;

        $user->update(['UserID' => 'CSADM']);
        $user->update(['Password' => BREncryption::hashUser($user, '2')]);

        $user->syncRolesWithBranch([$role], $branch->Id, $actor->Id);

        $this->command->info("Seeded admin user CSADM linked to branch [{$branch->Id}] {$branch->Name} with role [{$role->name}].");
    }
}