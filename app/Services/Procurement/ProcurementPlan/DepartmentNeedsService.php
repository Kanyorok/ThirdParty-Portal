<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeed;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Support\Facades\DB;

class DepartmentNeedsService
{
    protected ApprovalWorkflow $workflow;

    public function __construct(ApprovalWorkflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function create(array $data, User $actor): DepartmentNeed
    {
        $branchId = session('LoginBranchId');

        // Ensure the user has a linked employee record with a department
        $employee = $actor->employee;
        if (! $employee) {
            throw new \Exception('Your user account is not linked to an employee record. Please contact HR.');
        }

        $departmentId = $employee->DepartmentID;
        if (! $departmentId) {
            throw new \Exception('Your employee record does not have a department assigned. Please contact HR.');
        }

        $itemId = $data['ItemID'];

        // Prevent duplicate raise while a need is pending approval for same dept/branch/item
        $existing = DepartmentNeed::where('BranchID', $branchId)
            ->where('DepartmentID', $departmentId)
            ->where('ItemID', $itemId)
            ->where(
                'Status',
                DepartmentNeedsEnum::Pending,
                DepartmentNeedsEnum::Submitted->value
            )
            ->first();

        if ($existing) {
            throw new \Exception('A pending need for this item already exists for your department.');
        }

        // Generate NeedID safely
        $prefix = 'NEED-';
        $lastNEED = DepartmentNeed::where('NeedID', 'like', $prefix . '%')
            ->orderBy('Id', 'desc')
            ->first();
        $lastNumber = $lastNEED ? intval(substr($lastNEED->NeedID, strlen($prefix))) : 0;
        $newNEEDNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);


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
                'Status' => DepartmentNeedsEnum::Pending->value, // Start as sub,iited for approval
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
                DepartmentNeedsEnum::Pending,  // Required: Pending status enum
                'Submitted for approval'
            );

            activity()
                ->causedBy(modelOrId: $actor)
                ->performedOn($departmentNeed)
                ->event('create')
                ->log('Created and submitted Department Need ' . $departmentNeed->NeedID);



            return $departmentNeed;
        } catch (\Exception $e) {
            // Don't call DB::rollBack() here — the caller's DB::transaction() handles rollback.
            // Manually rolling back here conflicts with SP's SET XACT_ABORT ON which already
            // aborts all transactions on error.
            throw $e;
        }
    }
}
