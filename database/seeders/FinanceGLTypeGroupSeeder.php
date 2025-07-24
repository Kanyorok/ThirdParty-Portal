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
        ];

        foreach ($groups as [$code, $type, $desc]) {
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
