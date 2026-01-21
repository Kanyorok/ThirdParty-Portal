<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HROvertimeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = DB::table('t_HRJobGrades')->get(['Id']);
        $now = now();

        foreach ($grades as $grade) {
            DB::table('t_HROvertimeRates')->updateOrInsert(
                ['GradeID' => $grade->Id],
                [
                    'RateMultiplier' => 1.00,
                    'EffectiveFrom' => $now->toDateString(),
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                ]
            );
        }
    }
}
