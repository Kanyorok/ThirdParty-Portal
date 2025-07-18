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
            'A' => 'A', // Asset
            'L' => 'L', // Liability
        ];

        // Helper to get IDs from code
        $getGroupId = fn($code) =>
        DB::table('t_FinanceGLTypeGroups')->where('TypeGroupCode', $code)->value('Id');

        $getSubTypeId = fn($code) =>
        DB::table('t_FinanceGLSubAccountTypes')->where('SubAccountCode', $code)->value('Id');

        // GL Accounts to insert
        $glAccounts = [
            ['1000', 'Assets', 'A', 'CA', 'CA_CASH', null, 'DR', 1, 0, null, 005, 'Top level assets','1000','100','10','3'],
            ['1100', 'Cash at Bank', 'A', 'CA', 'CA_CASH', '1000', 'DR', 0, 1, 'CBS1001', 005, 'Bank account under assets','1000','200','20','3'],
            ['1200', 'Accounts Receivable', 'A', 'CA', 'CA_AR', '1000', 'DR', 0, 1, null, 005, 'Receivables from customers','1000','300','30','3'],
            ['2000', 'Liabilities', 'L', 'CL', 'CL_AP', null, 'CR', 1, 0, null, 005, 'Top level liabilities','2000','100','10','3'],
            ['2100', 'Accounts Payable', 'L', 'CL', 'CL_AP', '2000', 'CR', 0, 1, 'CBS2001', 005, 'Payables to suppliers','2000','200','20','3'],
        ];

        foreach ($glAccounts as $gl) {
            [$glCode, $name, $typeCode, $groupCode, $subCode, $parentCode, $normalBal, $isCtrl, $isPost, $cbsCode, $branch, $desc,$type,$accType,$subType,$glDigits] = $gl;

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
                'GLAccountTypeValue' => $type,
                'GLTypeGroupIDValue' =>$accType,
                'GLSubAccountTypeIDValue' => $subType,
                'GLDigits' => $glDigits,
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
