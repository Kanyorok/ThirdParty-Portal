<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Services\HRM\EmployeeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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
                throw new RuntimeException('No department found');
            }
            $branch = Branch::query()->first();
            if (!$branch instanceof Branch) {
                throw new RuntimeException('No branch found');
            }

            foreach ($users as $user) {
                if (SystemHelper::isSystem($user)){
                    continue;
                }

                $names = explode(' ', $user->Name);
                $employee = EmployeeService::create(department: $department, branch: $branch, actor: $actor,
                    JobTitle: 'ICT ADMIN', FirstName: $names[0], Surname: count($names)>0?$names[1]:'?',
                    Email: $user->Email, Phone: $user->Phone, JoinDate: now(), Gender: $user->Gender);
                if ($user->ImageId) {
                    $employee->employee->update(['ImageId' => $user->ImageId]);
                }
                $user->update(['EmployeeId' => $employee->employee->Id]);
            }
        });
    }
}
