<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use App\Models\BR\Branch;
use App\Models\CrmBranch;
use App\Models\Department;
use App\Models\User;
use App\Models\Employee;
use App\Services\HRM\EmployeeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $actor = SystemHelper::user();
            $users = User::all();
            $department = Department::query()->first();
            if (!$department instanceof Department) {
                throw new \RuntimeException('No department found');
            }
            $branch = CrmBranch::query()->first();
            if (!$branch instanceof CrmBranch) {
                throw new \RuntimeException('No branch found');
            }

            foreach ($users as $user) {
                if (SystemHelper::isSystem($user)){
                    continue;
                }

                $names = explode(' ', $user->Name);
                $employee = EmployeeService::create(department: $department, branch: $branch, actor: $actor,
                    JobTitle: 'ICT ADMIN', FirstName: $names[0], Surname: count($names)>0?$names[1]:'?',
                    Email: $user->Email, Phone: $user->Phone, JoinDate: now(), Gender: $user->Gender);

                $user->update(['EmployeeId' => $employee->employee->Id]);
            }
        });
    }
}
