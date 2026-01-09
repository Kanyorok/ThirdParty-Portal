<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceGlTransactionsMappingSeeder extends Seeder
{
    public function run(): void
    {
        $moduleId = 1100000; // Finance module ID
        $userId = 1;       // System/admin user ID for seeding
        $now = Carbon::now();

        // Mapping: TransactionTypeID, DebitGLCode, CreditGLCode
        $mappings = [
            [15, '5', '4'], // AP Invoice: Expense -> Accounts Payable
            [16, '2', '6'], // AR Invoice: Accounts Receivable -> Revenue
            [17, '4', '1'], // Voucher Posting: Accounts Payable -> Cash at Bank
            [18, '4', '7'], // Credit Note (Supplier): AP -> Expense reversal
            [19, '7', '4'], // Debit Note (Supplier): Expense -> AP
            [20, '1', '2'], // Receipts: Cash -> AR
            [21, '2', '4'], // Credit Management: AR -> AP
            [23, '2', '4'], // Interbranch Transfers: AR -> AP

        ];

        foreach ($mappings as [$transactionTypeId, $debitCode, $creditCode]) {
//            $debitId = DB::table('t_FinanceGLAccounts')->where('AccountCode', $debitCode)->value('Id');
//            $creditId = DB::table('t_FinanceGLAccounts')->where('AccountCode', $creditCode)->value('Id');

//            if (!$debitId || !$creditId) {
//                $this->command->warn("Skipping TransactionTypeID {$transactionTypeId} — GL not found ({$debitCode} or {$creditCode})");
//                continue;
//            }

            $exists = DB::table('t_FinanceGlTransactionsMapping')
                ->where('ModuleID', $moduleId)
                ->where('TransactionTypeID', $transactionTypeId)
                ->where('DebitGLAccountID', $debitCode)
                ->where('CreditGLAccountID', $creditCode)
                ->exists();

            if (!$exists) {
                DB::table('t_FinanceGlTransactionsMapping')->insert([
                    'ModuleID' => $moduleId,
                    'TransactionTypeID' => $transactionTypeId,
                    'DebitGLAccountID' => $debitCode,
                    'CreditGLAccountID' => $creditCode,
                    'IsActive' => 1,
                    'CreatedBy' => $userId,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => $now,
                ]);
            }
        }
    }
}
