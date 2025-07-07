<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Models\Procurement\DepartmentNeed;
use App\Models\Auth\User;
use Illuminate\Support\Str;

class DepartmentNeedsService
{
    public function create(array $data, User $actor): DepartmentNeed
    {
        //dd($actor->employee->DepartmentId);
        $prefix = 'NEED-';
        $lastNEED = DepartmentNeed::where('NeedID', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastNEED ? intval(substr($lastNEED->NeedID, strlen($prefix))) : 0;
        $newNEEDNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        $departmentNeed = DepartmentNeed::create([
            'NeedID' => $newNEEDNumber,
            'BranchID' => session('LoginBranchId'),
            'DepartmentID' => $actor->employee->DepartmentId,
            'ItemID' => $data['ItemID'],
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
