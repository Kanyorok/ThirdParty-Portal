<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkFlowTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $now = now();

        $workflowTypes = [
            [
                'TypeID' => 'AMT',
                'Name' => 'Amount-Based Approval',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'TypeID' => 'MAJ',
                'Name' => 'Majority Approval',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'TypeID' => 'ALL',
                'Name' => 'All Must Approve',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'TypeID' => 'CNT',
                'Name' => 'Count-Based Approval',
                'CreatedBy' => 2,
                'ModifiedBy' => 2,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
        ];

        // Insert data
        DB::table('t_WorkFlowTypes')->insert($workflowTypes);
    }
}
