<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceGLTypeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $groups = [
            ['FA', 'A', 'Fixed Assets'],
            ['CA', 'A', 'Current Assets'],
            ['CL', 'L', 'Current Liabilities'],
            ['LT', 'L', 'Long-Term Liabilities'],
            ['REV', 'I', 'Revenue'],
            ['OPEX', 'E', 'Operating Expenses'],
            ['CAPEX', 'E', 'Capital Expenditure'],
            ['SC', 'S', 'Capital'],
            ['RE', 'S', 'Retained Earnings'],
        ];

        foreach ($groups as [$code, $type, $desc]) {
            $exists = DB::table('t_FinanceGLTypeGroups')
                ->where('TypeGroupCode', $code)
                ->exists();

            if (!$exists) {
                DB::table('t_FinanceGLTypeGroups')->insert([
                    'TypeGroupCode'   => $code,
                    'GLAccountTypeId' => $type,
                    'Description'     => $desc,
                    'CreatedBy'       => 1,
                    'CreatedOn'       => $now,
                    'ModifiedBy'      => 1,
                    'ModifiedOn'      => $now,
                    'DeletedBy'       => null,
                    'DeletedOn'       => null,
                ]);
            }
        }
    }
}
