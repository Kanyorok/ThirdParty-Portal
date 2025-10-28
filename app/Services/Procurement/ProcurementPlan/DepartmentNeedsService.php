<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Models\Procurement\DepartmentNeed;
use App\Models\Auth\User;
use App\Services\Procurement\DepartmentNeedsWorkflow;
use App\Enums\Procurement\DepartmentNeedsEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentNeedsService
{
    protected DepartmentNeedsWorkflow $workflow;

    public function __construct(DepartmentNeedsWorkflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function create(array $data, User $actor): DepartmentNeed
    {
        $branchId = session('LoginBranchId');
        $departmentId = $actor->employee->DepartmentId;
        $itemId = $data['ItemID'];

        // Prevent duplicate raise while a need is pending approval for same dept/branch/item
        $existing = DepartmentNeed::where('BranchID', $branchId)
            ->where('DepartmentID', $departmentId)
            ->where('ItemID', $itemId)
            ->where('Status', DepartmentNeedsEnum::Pending)
            ->first();

        if ($existing) {
            throw new \Exception('A pending need for this item already exists for your department.');
        }

        // Generate NeedID
        $prefix = 'NEED-';
        $lastNEED = DepartmentNeed::where('NeedID', 'like', $prefix . '%')
            ->orderBy('Id', 'desc')
            ->first();
        $lastNumber = $lastNEED ? intval(substr($lastNEED->NeedID, strlen($prefix))) : 0;
        $newNEEDNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            // Create with Pending status - workflow will be initiated immediately
            $departmentNeed = DepartmentNeed::create([
                'NeedID' => $newNEEDNumber,
                'BranchID' => $branchId,
                'DepartmentID' => $departmentId,
                'ItemID' => $itemId,
                'RequestedQty' => $data['RequestedQty'],
                'EstimatedUnitCost' => $data['EstimatedUnitCost'],
                'Justification' => $data['Justification'],
                'Status' => DepartmentNeedsEnum::Pending->value, // Start as Pending
                'FiscalYear' => $data['FiscalYear'],
                'PriorityLevel' => $data['PriorityLevel'],
                'IsEmergency' => $data['IsEmergency'],
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'RequestedDate' => $data['RequestedDate'],
            ]);

            // Submit to workflow (this creates WorkflowHistory and WorkflowPending)
            $this->workflow->submit(
                $departmentNeed,
                $actor,
                $data['Justification'] ?? 'Department need submitted for approval'
            );

            activity()
                ->causedBy($actor)
                ->performedOn($departmentNeed)
                ->event('create')
                ->log('Created and submitted Department Need ' . $departmentNeed->NeedID);

            DB::commit();

            return $departmentNeed;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}