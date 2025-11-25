<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkFlowTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $actor = SystemHelper::user()->Id;
        $workflowTypes = [
            [
                'TypeID' => 'AMT',
                'Name' => 'Amount-Based Approval',
                'CreatedBy' => $actor,
                'ModifiedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'TypeID' => 'MAJ',
                'Name' => 'Majority Approval',
                'CreatedBy' => $actor,
                'ModifiedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'TypeID' => 'ALL',
                'Name' => 'All Must Approve',
                'CreatedBy' => $actor,
                'ModifiedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
            [
                'TypeID' => 'CNT',
                'Name' => 'Count-Based Approval',
                'CreatedBy' => $actor,
                'ModifiedBy' => $actor,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ],
        ];

        foreach ($workflowTypes as $type) {
            if (DB::table('t_WorkFlowTypes')->where('TypeID', $type['TypeID'])->exists()) {
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
