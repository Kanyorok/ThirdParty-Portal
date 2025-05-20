<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Models\Procurement\DepartmentNeeds;
use App\Models\Auth\User;
use Illuminate\Support\Str;

class DepartmentNeedsService
{
    public function create(array $data, User $actor): DepartmentNeeds
    {
    //dd($actor->employee->DepartmentId);
    $departmentNeed = DepartmentNeeds::create([
    'NeedID'=> 'NEED-' . Str::upper(Str::random(5)),
    'BranchID'          => $actor->employee->BranchId,
    'DepartmentID'      => $actor->employee->DepartmentId,
    'ItemID'            => $data['ItemID'],
    'RequestedQty'      => $data['RequestedQty'],
    'EstimatedUnitCost' => $data['EstimatedUnitCost'],
    'Justification'     => $data['Justification'],
    'Status'            => $data['Status'],
    'FiscalYear'        => $data['FiscalYear'],
    'PriorityLevel'     => $data['PriorityLevel'],
    'IsEmergency'       => $data['IsEmergency'],
    'CreatedBy'         => $actor->Id,
    'ModifiedBy'        => $actor->Id,
    'RequestedDate'     => $data['RequestedDate'],
    ]);

        activity()
            ->causedBy($actor)
            ->performedOn($departmentNeed)
            ->event('create')
            ->log('Created Department Needs ' . $departmentNeed->NeedID);

        return $departmentNeed;
    }
}
