<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkflowTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

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

        foreach ($workflowTypes as $type) {
            $existing = DB::table('t_WorkFlowTypes')->where('TypeID', $type['TypeID'])->first();

            if ($existing) {
                DB::table('t_WorkFlowTypes')
                    ->where('TypeID', $type['TypeID'])
                    ->update([
                        'Name' => $type['Name'],
                        'ModifiedBy' => $type['ModifiedBy'],
                        'ModifiedOn' => $type['ModifiedOn'],
                    ]);
            } else {
                DB::table('t_WorkFlowTypes')->insert($type);
            }
        }
    }
}
