<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            ['CL_LIAB', 'CL', 'Liabilities'],
            ['CL_EXP', 'CL', 'Expense Payable'],

            // Revenue
            ['REV_SALES', 'REV', 'Sales Revenue'],
            ['REV_FEES', 'REV', 'Service Fees'],
            ['RV_INCOME', 'REV', 'Income'],

            // Expenses
            ['OPEX_SAL', 'OPEX', 'Salaries'],
            ['EX_EXP', 'OPEX', 'Purchases or operating expenses'],
            ['OPEX_RENT', 'OPEX', 'Rent Expense'],
            ['CAPEX_SOFT', 'CAPEX', 'Software Licenses'],

            // Share and Capital
            ['EQ_SHARECAP', 'SC', 'Share Capital'],
            ['EQ_RETAINED', 'RE', 'Retained Earnings'],
        ];

        foreach ($subAccounts as [$code, $typeGroupCode, $desc]) {
            // Check if SubAccountCode already exists
            $exists = DB::table('t_FinanceGLSubAccountTypes')
                ->where('SubAccountCode', $code)
                ->exists();

            if ($exists) {
                continue; // Skip duplicates
            }

            $typeGroup = DB::table('t_FinanceGLTypeGroups')
                ->where('TypeGroupCode', $typeGroupCode)
                ->first();

            if ($typeGroup) {
                DB::table('t_FinanceGLSubAccountTypes')->insert([
                    'SubAccountCode' => $code,
                    'GLTypeGroupId' => $typeGroup->Id,
                    'Description' => $desc,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                ]);
            }
        }
    }
}
