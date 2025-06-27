<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcurementRequisitionSeeder extends Seeder
{
    public function run(): void
    {
        $statusId = DB::table('t_CodeDetails')->where('CodeID', 'a')->value('ID');
        $branchId = DB::table('t_Branches')->value('Id');
        $departmentId = DB::table('t_Departments')->value('Id');
        $userId = DB::table('t_Users')->value('Id');

        if (!$statusId || !$branchId || !$departmentId || !$userId) {
            dump('Missing required related data for seeding.');
            return;
        }

        DB::table('t_Requisitions')->insert([
            'RequisitionNo' => 'REQ-2025-003',
            'BranchID' => $branchId,
            'DepartmentID' => $departmentId,
            'Remarks' => 'Seeder-generated test requisition',
            'StatusID' => $statusId,
            'PlanRef' => 200,
            'CreatedBy' => $userId,
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => $userId,
            'ModifiedOn' => Carbon::now(),
            'DeletedBy' => null,
        ]);
    }
}
