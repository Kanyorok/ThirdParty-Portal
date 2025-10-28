<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Models\Procurement\DepartmentNeed;
use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

        // Generate NeedID safely (robust to deletions and concurrent requests)
        $prefix = 'NEED-';
        $padLen = 5;

        $departmentNeed = null;
        $lock = Cache::lock('departmentneeds-next-needid', 5);
    $lock->block(5, function () use (&$departmentNeed, $prefix, $padLen, $branchId, $departmentId, $itemId, $data, $actor) {
            // Compute the max numeric part from both prefixed and legacy numeric NeedIDs
            $maxNo = DB::table('t_DepartmentNeeds')
                ->selectRaw("MAX(TRY_CONVERT(int, REPLACE(NeedID, ?, ''))) as max_no", [$prefix])
                ->value('max_no');

            $nextNo = ((int) ($maxNo ?? 0)) + 1;
            $newNeedId = $prefix . str_pad($nextNo, $padLen, '0', STR_PAD_LEFT);

            // Create inside the lock to avoid race conditions
            $departmentNeed = DepartmentNeed::create([
                'NeedID' => $newNeedId,
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
        });

        if (!$departmentNeed) {
            throw new \RuntimeException('Failed to generate a unique NeedID. Please try again.');
        }

        activity()
            ->causedBy($actor)
            ->performedOn($departmentNeed)
            ->event('create')
            ->log('Created Department Needs ' . $departmentNeed->NeedID);

        return $departmentNeed;
    }

}
