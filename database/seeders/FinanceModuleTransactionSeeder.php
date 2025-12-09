<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceModuleTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Mapping: TransactionTypeID => ModuleID
        $mappings = [
            1 => 300000,
            2 => 300000,
            3 => 300000,
            4 => 300000,
            5 => 400000,
            6 => 400000,
            7 => 500000,
            8 => 500000,
            9 => 600000,
            10 => 600000,
            11 => 800000,
            12 => 800000,
            13 => 900000,
            14 => 900000,

            //Finance
            15 => 1100000,
            16 => 1100000,
            17 => 1100000,
            18 => 1100000,
            19 => 1100000,
            20 => 1100000,
            21 => 1100000,
            22 => 1100000,

        ];

        // Build the insert array
        $insertData = [];
        foreach ($mappings as $transactionTypeId => $moduleId) {
            $insertData[] = [
                'ModuleID' => $moduleId,
                'TransactionTypeID' => $transactionTypeId,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ];
        }

        // Insert into the table
        DB::table('t_FinanceModuleTransactions')->insert($insertData);
    }
}
