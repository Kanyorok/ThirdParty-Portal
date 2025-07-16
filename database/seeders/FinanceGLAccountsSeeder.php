<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceGLAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // AccountType code => ID map (manually or use a lookup if many)
        $accountTypeMap = [
            'A' => 1, // Asset
            'L' => 2, // Liability
        ];

        // Helper to get IDs from code
        $getGroupId = fn($code) =>
        DB::table('t_FinanceGLTypeGroups')->where('TypeGroupCode', $code)->value('Id');

        $getSubTypeId = fn($code) =>
        DB::table('t_FinanceGLSubAccountTypes')->where('SubAccountCode', $code)->value('Id');

        // GL Accounts to insert
        $glAccounts = [
            ['1000', 'Assets', 'A', 'CA', 'CA_CASH', null, 'DR', 1, 0, null, 1, 'Top level assets'],
            ['1100', 'Cash at Bank', 'A', 'CA', 'CA_CASH', '1000', 'DR', 0, 1, 'CBS1001', 1, 'Bank account under assets'],
            ['1200', 'Accounts Receivable', 'A', 'CA', 'CA_AR', '1000', 'DR', 0, 1, null, 1, 'Receivables from customers'],
            ['2000', 'Liabilities', 'L', 'CL', 'CL_AP', null, 'CR', 1, 0, null, 1, 'Top level liabilities'],
            ['2100', 'Accounts Payable', 'L', 'CL', 'CL_AP', '2000', 'CR', 0, 1, 'CBS2001', 1, 'Payables to suppliers'],
        ];

        foreach ($glAccounts as $gl) {
            [$glCode, $name, $typeCode, $groupCode, $subCode, $parentCode, $normalBal, $isCtrl, $isPost, $cbsCode, $branch, $desc] = $gl;

            $typeId    = $accountTypeMap[$typeCode];
            $groupId   = $getGroupId($groupCode);
            $subTypeId = $getSubTypeId($subCode);

            // Resolve parent ID (if any)
            $parentId = null;
            if ($parentCode) {
                $parentId = DB::table('t_FinanceGLAccounts')->where('GLCode', $parentCode)->value('Id');
            }

            DB::table('t_FinanceGLAccounts')->insert([
                'GLCode'             => $glCode,
                'GLName'             => $name,
                'GLAccountTypeID'    => $typeId,
                'GLTypeGroupID'      => $groupId,
                'GLSubAccountTypeID' => $subTypeId,
                'ParentGLID'         => $parentId,
                'NormalBalance'      => $normalBal,
                'IsControlAccount'   => $isCtrl,
                'IsPostingAccount'   => $isPost,
                'CBSAccountCode'     => $cbsCode,
                'BranchID'           => $branch,
                'Description'        => $desc,
                'IsActive'           => 1,
                'CreatedBy'          => 1,
                'CreatedOn'          => $now,
                'ModifiedBy'         => 1,
                'ModifiedOn'         => $now,
                'DeletedBy'          => null,
                'DeletedOn'          => null,
            ]);
        }
    }
}
