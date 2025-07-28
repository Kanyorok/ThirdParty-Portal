<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceGLSubAccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Mapping TypeGroupCode to SubAccount data
        $subAccounts = [
            // Fixed Assets
            ['FA_BLDG', 'FA', 'Buildings'],
            ['FA_EQP', 'FA', 'Equipment'],
            ['FA_VEH', 'FA', 'Vehicles'],

            // Current Assets
            ['CA_CASH', 'CA', 'Cash and Bank'],
            ['CA_AR', 'CA', 'Accounts Receivable'],
            ['CA_INV', 'CA', 'Inventory'],

            // Current Liabilities
            ['CL_AP', 'CL', 'Accounts Payable'],
            ['CL_TAX', 'CL', 'Tax Payable'],

            // Revenue
            ['REV_SALES', 'REV', 'Sales Revenue'],
            ['REV_FEES', 'REV', 'Service Fees'],

            // Expenses
            ['OPEX_SAL', 'OPEX', 'Salaries'],
            ['OPEX_RENT', 'OPEX', 'Rent Expense'],
            ['CAPEX_SOFT', 'CAPEX', 'Software Licenses'],
        ];

        foreach ($subAccounts as [$code, $typeGroupCode, $desc]) {
            $typeGroup = DB::table('t_FinanceGLTypeGroups')
                ->where('TypeGroupCode', $typeGroupCode)
                ->first();

            if ($typeGroup) {
                DB::table('t_FinanceGLSubAccountTypes')->insert([
                    'SubAccountCode'  => $code,
                    'GLTypeGroupId'   => $typeGroup->Id,
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
