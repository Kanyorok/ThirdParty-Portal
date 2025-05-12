<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Procurement\Requisitions;
use Carbon\Carbon;

class RequisitionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Requisitions::insert([
            [
                'RequisitionNo' => 'REQ001',
                'BranchID' => 'Head Office',
                'DepartmentID' => 'Procurement',
                'Remarks' => 'Office supplies needed urgently',
                'Category' => 'Stationery',
                'Category' => 1,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => Carbon::now(),
                'ModifiedOn' => Carbon::now(),
            ],
            [
                'RequisitionNo' => 'REQ002',
                'BranchID' => 'Regional Office',
                'DepartmentID' => 'IT',
                'Remarks' => 'Laptops for new staff',
                'Category' => 'Electronics',
                'Category' => 2,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => Carbon::now(),
                'ModifiedOn' => Carbon::now(),
            ],
        ]);
    }
}
