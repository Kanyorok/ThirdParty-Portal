<?php

namespace Database\Seeders;

use App\Enums\Employee\GenderEnum;
use App\Helpers\SystemHelper;
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
            throw new RuntimeException('No branch found');
        }

        $user = EmployeeService::create(
            department: $department,
            branch: $branch,
            actor: $actor,
            JobTitle: 'ICT ADMIN',
            FirstName: 'Default',
            Surname: 'User',
            Email: "admin@test.co.ke",
            Phone: '254700100100',
            JoinDate: now(),
            Gender: GenderEnum::Other
        )
            ->createUser($actor)->setRole($role, $branch, $actor)->user->refresh();

        $user->update([
            'UserID' => 'CSADM',
        ]);
        $user->update([
            'Password' => BREncryption::hashUser($user, '2'),
        ]);
    }
}
