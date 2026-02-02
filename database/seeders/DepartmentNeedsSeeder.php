<?php

namespace Database\Seeders;

use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\Inventory\ItemMasterList;
use App\Models\Procurement\DepartmentNeed;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DepartmentNeedsSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $createdBy = 2;  // Assuming user 2 is admin/seeder

        // Fetch sample IDs from related tables (just pick some for seeding)
        $branchIds = Branch::pluck('Id')->toArray();
        $departmentIds = Department::pluck('Id')->toArray();
        $itemIds = ItemMasterList::pluck('Id')->toArray();

        if (empty($branchIds) || empty($departmentIds) || empty($itemIds)) {
            $this->command->warn('Branches, Departments, or Items missing. Seed those first.');

            return;
        }

        // Get the current max NeedID to avoid duplicates
        $maxNeedId = DepartmentNeed::max('NeedID') ?? 0;

        // Example data to seed
        $needs = [
            [
                'BranchID' => $branchIds[array_rand($branchIds)],
                'DepartmentID' => $departmentIds[array_rand($departmentIds)],
                'ItemID' => $itemIds[array_rand($itemIds)],
                'RequestedQty' => 10,
                'EstimatedUnitCost' => 150.00,
                'Justification' => 'Needed for new project setup',
                'Status' => 'p',
                'FiscalYear' => date('Y'),
                'RequestedDate' => $now->subDays(5),
                'PriorityLevel' => 1,
                'IsEmergency' => false,
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now->subDays(5),
                'ModifiedOn' => $now->subDays(5),
            ],
            [
                'BranchID' => $branchIds[array_rand($branchIds)],
                'DepartmentID' => $departmentIds[array_rand($departmentIds)],
                'ItemID' => $itemIds[array_rand($itemIds)],
                'RequestedQty' => 5,
                'EstimatedUnitCost' => 350.00,
                'Justification' => 'Replacement of worn-out equipment',
                'Status' => 'a',
                'FiscalYear' => date('Y'),
                'RequestedDate' => $now->subDays(10),
                'PriorityLevel' => 2,
                'IsEmergency' => true,
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now->subDays(10),
                'ModifiedOn' => $now->subDays(2),
            ],
            [
                'BranchID' => $branchIds[array_rand($branchIds)],
                'DepartmentID' => $departmentIds[array_rand($departmentIds)],
                'ItemID' => $itemIds[array_rand($itemIds)],
                'RequestedQty' => 20,
                'EstimatedUnitCost' => 75.00,
                'Justification' => 'Bulk purchase for office supplies',
                'Status' => 'p',
                'FiscalYear' => date('Y'),
                'RequestedDate' => $now->subDays(15),
                'PriorityLevel' => 3,
                'IsEmergency' => false,
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now->subDays(15),
                'ModifiedOn' => $now->subDays(5),
            ],
            [
                'BranchID' => $branchIds[array_rand($branchIds)],
                'DepartmentID' => $departmentIds[array_rand($departmentIds)],
                'ItemID' => $itemIds[array_rand($itemIds)],
                'RequestedQty' => 8,
                'EstimatedUnitCost' => 200.00,
                'Justification' => 'New software licenses for team',
                'Status' => 'p',
                'FiscalYear' => date('Y'),
                'RequestedDate' => $now->subDays(20),
                'PriorityLevel' => 1,
                'IsEmergency' => false,
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now->subDays(20),
                'ModifiedOn' => $now->subDays(10),
            ],
            [
                'BranchID' => $branchIds[array_rand($branchIds)],
                'DepartmentID' => $departmentIds[array_rand($departmentIds)],
                'ItemID' => $itemIds[array_rand($itemIds)],
                'RequestedQty' => 15,
                'EstimatedUnitCost' => 500.00,
                'Justification' => 'Upgrade of existing hardware',
                'Status' => 'a',
                'FiscalYear' => date('Y'),
                'RequestedDate' => $now->subDays(30),
                'PriorityLevel' => 2,
                'IsEmergency' => true,
                'CreatedBy' => $createdBy,
                'ModifiedBy' => $createdBy,
                'CreatedOn' => $now->subDays(30),
                'ModifiedOn' => $now->subDays(20),
            ],
            // Add more sample needs if you want...
        ];

        foreach ($needs as $need) {
            $maxNeedId++;
            $need['NeedID'] = $maxNeedId; // Assign unique NeedID
            DepartmentNeed::create($need);
        }
    }
}
