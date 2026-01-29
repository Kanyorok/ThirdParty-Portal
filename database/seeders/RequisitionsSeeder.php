<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequisitionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('t_Requisitions')->insert([
            [
                'RequisitionNo' => 'REQ-2025-001',
                'BranchID' => 'BR001',
                'DepartmentID' => 'IT',
                'Remarks' => 'Urgent request for new laptops for the IT department.',
                'StatusID' => 1, // Assumes a status with ID 1 (e.g., 'Pending') exists in t_CodeDetails
                'CreatedBy' => 1, // Assumes user ID 1 exists in t_Users
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'RequisitionNo' => 'REQ-2025-002',
                'BranchID' => 'BR002',
                'DepartmentID' => 'HR',
                'Remarks' => 'Request for construction of new staff quarters.',
                'StatusID' => 2, // Assumes a status with ID 2 (e.g., 'Approved') exists in t_CodeDetails
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'RequisitionNo' => 'REQ-2025-003',
                'BranchID' => 'BR001',
                'DepartmentID' => 'IT',
                'Remarks' => 'Consultancy services for IT system upgrade.',
                'StatusID' => 1, // Assumes a status with ID 1 (e.g., 'Pending') exists in t_CodeDetails
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
        ]);
    }
}
