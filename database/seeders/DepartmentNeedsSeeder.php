<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DepartmentNeedsSeeder extends Seeder
{
    public function run()
    {
        DB::table('t_DepartmentNeeds')->insert([
            'NeedID' => 'NEED-' . Str::upper(Str::random(8)),
            'BranchID' => 1,
            'DepartmentID' => 1,
            'ItemID' => 4,
            'RequestedQty' => 50,
            'EstimatedUnitCost' => 125.75,
            'Justification' => 'Urgent requirement for departmental operations.',
            'Status' => 'Pending',
            'FiscalYear' => 2025,
            'PriorityLevel' => 'Normal',
            'IsEmergency' => false,
            'RequestedDate' => Carbon::now(), // ✅ Include this
            'CreatedBy' => 1,
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => 1,
            'ModifiedOn' => Carbon::now(),
            'DeletedBy' => null,
            'DeletedOn' => null,
        ]);
    }
}