<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\InterBranchRequisitionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class InterBranchRequisitionService
{
    public function create(array $data): InterBranchRequisition
    {
       
        $items = $data['items'] ?? [];
        unset($data['items']);

        $requisition = new InterBranchRequisition($data);
        $requisition->CreatedBy = Auth::id();
        $requisition->ModifiedBy = Auth::id();
        $requisition->CreatedOn = Carbon::now();
        $requisition->ModifiedOn = Carbon::now();
        $requisition->save();
        $requisition->ReqNo = $this->generateReqNo($requisition);
        $requisition->save();

        
        foreach ($items as $item) {
            $item['RequisitionId'] = $requisition->Id;
            InterBranchRequisitionItem::create($item);
        }

        activity()
            ->performedOn($requisition)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data, 'items' => $items])
            ->log('Created InterBranch Requisition');

        return $requisition;
    }

    public function update(InterBranchRequisition $requisition, array $data): InterBranchRequisition
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $requisition->fill($data);
        $requisition->ModifiedBy = Auth::id();
        $requisition->ModifiedOn = Carbon::now();
        $requisition->save();

       
        $requisition->items()->delete();

        foreach ($items as $item) {
            $item['RequisitionId'] = $requisition->Id;
            InterBranchRequisitionItem::create($item);
        }

        activity()
            ->performedOn($requisition)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data, 'items' => $items])
            ->log('Updated InterBranch Requisition');

        return $requisition;
    }

    public function delete(InterBranchRequisition $requisition): bool
    {
        $requisition->DeletedBy = Auth::id();
        $requisition->save();
        $requisition->delete();

       
        $requisition->items()->delete();

        activity()
            ->performedOn($requisition)
            ->causedBy(Auth::user())
            ->log('Deleted InterBranch Requisition');

        return true;
    }

  
    protected function generateReqNo(InterBranchRequisition $requisition): string
    {
        $year = now()->format('Y');
        return 'REQ-' . $year . '-' . str_pad($requisition->Id, 4, '0', STR_PAD_LEFT);
    }
}