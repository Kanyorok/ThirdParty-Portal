<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceModuleTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Define the configuration: ModuleID => [TransactionTypeIDs]
        $moduleConfig = [
            300000 => range(1, 4),      // 1, 2, 3, 4
            400000 => [5, 6],
            500000 => [7, 8],
            600000 => [9, 10],
            800000 => [11, 12],
            900000 => [13, 14],
            1100000 => range(15, 22),    // 15 through 22
        ];

        $data = [];

        foreach ($moduleConfig as $moduleId => $transactionTypes) {
            foreach ($transactionTypes as $typeId) {
                $data[] = [
                    'ModuleID' => $moduleId,
                    'TransactionTypeID' => $typeId,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                ];
            }
        }

        // upsert prevents duplicates.
        // Note: Requires a unique key/index on ['ModuleID', 'TransactionTypeID'] in your DB.
        DB::table('t_FinanceModuleTransactions')->upsert(
            $data,
            ['ModuleID', 'TransactionTypeID'], // The unique columns
            ['ModifiedOn', 'ModifiedBy']       // Columns to update if record exists
        );
    }
}
