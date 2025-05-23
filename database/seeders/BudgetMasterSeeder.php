<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BudgetMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('t_BudgetMaster')->insert([
            [
                'BudgetLineID'     => 1,
                'Code'             => 'BL-001',
                'Description'      => 'Operations Budget',
                'AllocatedAmount'  => 1500000.00,
                'FiscalYear'       => '2024/2025',
                'CreatedBy'        => 1, // Assumes user ID 1 exists
                'CreatedOn'        => Carbon::now(),
                'ModifiedBy'       => 1,
                'ModifiedOn'       => Carbon::now(),
                'DeletedBy'        => null,
                'DeletedOn'        => null,
            ],
            [
                'BudgetLineID'     => 2,
                'Code'             => 'BL-002',
                'Description'      => 'Development Projects',
                'AllocatedAmount'  => 5000000.00,
                'FiscalYear'       => '2024/2025',
                'CreatedBy'        => 1,
                'CreatedOn'        => Carbon::now(),
                'ModifiedBy'       => 1,
                'ModifiedOn'       => Carbon::now(),
                'DeletedBy'        => null,
                'DeletedOn'        => null,
            ],
        ]);
    }
}
