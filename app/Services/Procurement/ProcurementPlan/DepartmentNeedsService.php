<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Models\Procurement\DepartmentNeed;
use App\Models\Auth\User;
use Illuminate\Support\Str;

class DepartmentNeedsService
{
    public function create(array $data, User $actor): DepartmentNeed
    {
        $branchId = session('LoginBranchId');
        $departmentId = $actor->employee->DepartmentId;
        $itemId = $data['ItemID'];

        // Check for existing need with same combination
        $existing = DepartmentNeed::where('BranchID', $branchId)
            ->where('DepartmentID', $departmentId)
            ->where('ItemID', $itemId)
            ->first();

        if ($existing) {
            throw new \Exception('A need for this item already exists in this branch and department.');
        }

        // Generate NeedID
        $prefix = 'NEED-';
        $lastNEED = DepartmentNeed::where('NeedID', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastNEED ? intval(substr($lastNEED->NeedID, strlen($prefix))) : 0;
        $newNEEDNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        // Create the new department need
        $departmentNeed = DepartmentNeed::create([
            'NeedID' => $newNEEDNumber,
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

        activity()
            ->causedBy($actor)
            ->performedOn($departmentNeed)
            ->event('create')
            ->log('Created Department Needs ' . $departmentNeed->NeedID);

        return $departmentNeed;
    }

}
