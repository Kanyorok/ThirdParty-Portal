<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobGradeSeeder extends Seeder
{
    public function run(): void
    {
        $actor = SystemHelper::user();
        $now = now();

        $grades = [
            ['Code' => 'B10', 'Name' => 'Board Chair'],
            ['Code' => 'B09', 'Name' => 'Board Member'],
            ['Code' => 'E08', 'Name' => 'CEO'],
            ['Code' => 'E07', 'Name' => 'Deputy CEO'],
            ['Code' => 'M06', 'Name' => 'Chief Officer'],
            ['Code' => 'M05', 'Name' => 'GM / Head'],
            ['Code' => 'M04', 'Name' => 'Senior Manager'],
            ['Code' => 'M03', 'Name' => 'Manager'],
            ['Code' => 'S02', 'Name' => 'Assistant Manager'],
            ['Code' => 'S01', 'Name' => 'Supervisor'],
            ['Code' => 'O00', 'Name' => 'Senior Officer'],
            ['Code' => 'O-1', 'Name' => 'Officer'],
            ['Code' => 'O-2', 'Name' => 'Entry Officer'],
            ['Code' => 'T01', 'Name' => 'Graduate Trainee'],
            ['Code' => 'I01', 'Name' => 'Intern'],
        ];

        foreach ($grades as $grade) {
            DB::table('t_HRJobGrades')->updateOrInsert(
                ['Code' => $grade['Code']],
                [
                    'Name' => $grade['Name'],
                    'MinSalary' => null,
                    'MaxSalary' => null,
                    'Description' => null,
                    'IsActive' => 1,
                    'CreatedBy' => $actor->Id ?? 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor->Id ?? 1,
                    'ModifiedOn' => $now,
                ]
            );
        }
    }
}
