<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InterBranchRequisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class InterBranchRequisitionService
{
    public function create(array $data): InterBranchRequisition
    {
        static $reqNo = null;

        if (!$reqNo) {
            $reqNo = $this->generateReqNo();
        }

        $requisition = new InterBranchRequisition($data);
        $requisition->ReqNo = $reqNo;
        $requisition->CreatedBy = Auth::id();
        $requisition->ModifiedBy = Auth::id();
        $requisition->CreatedOn = Carbon::now();
        $requisition->ModifiedOn = Carbon::now();
        $requisition->save();

        activity()
            ->performedOn($requisition)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data])
            ->log('Created InterBranch Requisition');

        return $requisition;
    }

    public function update(InterBranchRequisition $requisition, array $data): InterBranchRequisition
    {
        $requisition->fill($data);
        $requisition->ModifiedBy = Auth::id();
        $requisition->ModifiedOn = Carbon::now();
        $requisition->save();

        activity()
            ->performedOn($requisition)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data])
            ->log('Updated InterBranch Requisition');

        return $requisition;
    }

    public function delete(InterBranchRequisition $requisition): bool
    {
        $requisition->DeletedBy = Auth::id();
        $requisition->save();
        $requisition->delete();

        activity()
            ->performedOn($requisition)
            ->causedBy(Auth::user())
            ->log('Deleted InterBranch Requisition');

        return true;
    }

    protected function generateReqNo(): string
    {
        $lastId = InterBranchRequisition::withTrashed()->max('Id') ?? 0;
        return 'REQ-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
