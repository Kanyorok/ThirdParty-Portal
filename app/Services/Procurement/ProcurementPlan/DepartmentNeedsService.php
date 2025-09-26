<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Models\Procurement\DepartmentNeed;
use App\Models\Auth\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DepartmentNeedsService
{
    public function create(array $data, User $actor): DepartmentNeed
    {
        $branchId = session('LoginBranchId');
        $departmentId = $actor->employee->DepartmentId;
        $itemId = $data['ItemID'];

        // Prevent duplicate raise while a need is pending approval for same dept/branch/item
        $existing = DepartmentNeed::where('BranchID', $branchId)
            ->where('DepartmentID', $departmentId)
            ->where('ItemID', $itemId)
            ->where('Status', \App\Enums\Procurement\DepartmentNeedsEnum::Pending)
            ->first();

        if ($existing) {
            throw new \Exception('A pending need for this item already exists for your department.');
        }

        // Two-phase NeedID assignment to guarantee uniqueness and ordering with identity:
        // 1) Insert with a temporary unique placeholder
        $tempNeedId = 'NEED-TMP-' . (string) Str::uuid();

        $departmentNeed = DepartmentNeed::create([
            'NeedID' => $tempNeedId,
            'BranchID' => $branchId,
            'DepartmentID' => $departmentId,
            'ItemID' => $itemId,
            'RequestedQty' => $data['RequestedQty'],
            'EstimatedUnitCost' => $data['EstimatedUnitCost'],
            'Justification' => $data['Justification'],
            'Status' => $data['Status'],
            'FiscalYear' => $data['FiscalYear'],
            'PriorityLevel' => $data['PriorityLevel'],
            'IsEmergency' => $data['IsEmergency'],
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
            'RequestedDate' => $data['RequestedDate'],
        ]);

        // 2) Update NeedID using the assigned identity to ensure monotonic, collision-free ids
        $finalNeedId = 'NEED-' . str_pad((string) $departmentNeed->Id, 5, '0', STR_PAD_LEFT);
        $departmentNeed->update(['NeedID' => $finalNeedId]);

        activity()
            ->causedBy($actor)
            ->performedOn($departmentNeed)
            ->event('create')
            ->log('Created Department Needs ' . $departmentNeed->NeedID);

        return $departmentNeed;
    }

}
