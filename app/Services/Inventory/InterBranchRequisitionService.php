<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\InterBranchRequisitionEnum;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\InterBranchRequisitionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 

class InterBranchRequisitionService
{
    protected function generateReqNo(InterBranchRequisition $requisition): string
    {
        $year = now()->format('Y');
        return 'REQ-' . $year . '-' . str_pad($requisition->Id, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): InterBranchRequisition
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        // Always use the enum for status
        $requisition = new InterBranchRequisition($data);
        $requisition->Status = InterBranchRequisitionEnum::Submitted->value;
        $requisition->CreatedBy = Auth::id();
        $requisition->ModifiedBy = Auth::id();
        $requisition->CreatedOn = Carbon::now();
        $requisition->ModifiedOn = Carbon::now();
        $requisition->save();

       
        $requisition->ReqNo = $this->generateReqNo($requisition);
        $requisition->save(); 

        foreach ($items as $item) {
            $item['RequisitionId'] = $requisition->Id;
            $item['CreatedBy'] = Auth::id();
            $item['ModifiedBy'] = Auth::id();
            $item['CreatedOn'] = Carbon::now();
            $item['ModifiedOn'] = Carbon::now();
            InterBranchRequisitionItem::create($item);
        }

        Workflow::create([
            'Source' => 'InterBranchRequisition',
            'SourceID' => $requisition->Id,
            'Stage' => InterBranchRequisitionEnum::Submitted->label(),
            'Status' => InterBranchRequisitionEnum::Submitted->value,
            'Notes' => 'Requisition submitted, awaiting approval',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
        ]);


        PendingWorkflow::updateOrCreate(
            [
                'Source' => 'InterBranchRequisition',
                'SourceID' => $requisition->Id,
            ],
            [
                'Stage' => InterBranchRequisitionEnum::Submitted->label(),
                'UserId' => Auth::id(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => Carbon::now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now(),
            ]
        );

        activity()
            ->performedOn($requisition)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data, 'items' => $items])
            ->log('Created InterBranch Requisition');

        return $requisition;
    }

    public function update(InterBranchRequisition $requisition, array $data): InterBranchRequisition
    {
        $submittedItemsData = $data['items'] ?? [];
        unset($data['items']);

        // Update the main requisition record
        $requisition->fill($data);
        $requisition->ModifiedBy = Auth::id();
        $requisition->ModifiedOn = Carbon::now();
        $requisition->save();

        // Get current item IDs for this requisition
        $existingItemIds = $requisition->items->pluck('Id')->toArray();
        $itemsToKeepIds = [];

        foreach ($submittedItemsData as $itemData) {
            // Check if 'Id' exists and is not empty, indicating an existing item being updated
            if (isset($itemData['Id']) && !empty($itemData['Id'])) {
                $itemsToKeepIds[] = $itemData['Id']; // Mark this ID to be kept
                $existingItem = InterBranchRequisitionItem::find($itemData['Id']); // Find by primary key directly

                if ($existingItem) {
                    // Update existing item
                    $existingItem->fill($itemData);
                    $existingItem->ModifiedBy = Auth::id();
                    $existingItem->ModifiedOn = Carbon::now();
                    $existingItem->save();
                } else {

                    $itemData['RequisitionId'] = $requisition->Id;
                    $itemData['CreatedBy'] = Auth::id();
                    $itemData['ModifiedBy'] = Auth::id();
                    $itemData['CreatedOn'] = Carbon::now();
                    $itemData['ModifiedOn'] = Carbon::now();
                    InterBranchRequisitionItem::create($itemData);
                }
            } else {
                // This is a new item (no 'Id' or 'Id' is empty), create it
                $itemData['RequisitionId'] = $requisition->Id;
                $itemData['CreatedBy'] = Auth::id();
                $itemData['ModifiedBy'] = Auth::id();
                $itemData['CreatedOn'] = Carbon::now();
                $itemData['ModifiedOn'] = Carbon::now();
                InterBranchRequisitionItem::create($itemData);
            }
        }

        // Determine which existing items were removed from the form
        $itemsToDelete = array_diff($existingItemIds, $itemsToKeepIds);

        // Delete items that are no longer present in the submitted data
        if (!empty($itemsToDelete)) {
            InterBranchRequisitionItem::whereIn('Id', $itemsToDelete)->delete();
        }

        activity()
            ->performedOn($requisition)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data, 'items' => $submittedItemsData])
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

    public function submitDecision(
        InterBranchRequisition $requisition,
        string                 $action,
        string                 $comments,
        array                  $approvedQty = [],
        array                  $itemRemarks = [],
                               $user = null
    ): void
    {
        $user = $user ?: Auth::user();

        if (!empty($approvedQty)) {
            foreach ($approvedQty as $itemId => $qty) {
                $item = $requisition->items()->find($itemId);
                if ($item) {
                    $item->ApprovedQty = $qty;
                    $item->Remarks = $itemRemarks[$itemId] ?? $item->Remarks;
                    $item->ModifiedBy = $user->Id;
                    $item->ModifiedOn = Carbon::now();
                    $item->save();
                }
            }
        }


        if ($action === 'APPROVED') {
            $requisition->Status = InterBranchRequisitionEnum::Approved->value;
        } elseif ($action === 'REJECTED') {
            $requisition->Status = InterBranchRequisitionEnum::Rejected->value;
        }
        $requisition->ModifiedBy = $user->Id;
        $requisition->ModifiedOn = Carbon::now();
        $requisition->save();


        $enum = match ($action) {
            'APPROVED' => InterBranchRequisitionEnum::Approved,
            'REJECTED' => InterBranchRequisitionEnum::Rejected,
            default => InterBranchRequisitionEnum::Pending
        };

        Workflow::create([
            'Source' => 'InterBranchRequisition',
            'SourceID' => $requisition->Id,
            'Stage' => $enum->label(),
            'Status' => $enum->value,
            'Notes' => $comments,
            'CreatedBy' => $user->Id,
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => $user->Id,
            'ModifiedOn' => Carbon::now(),
        ]);

        $pending = PendingWorkflow::where([
            'Source' => 'InterBranchRequisition',
            'SourceID' => $requisition->Id,
        ])->first();

        if ($pending) {
            $pending->UserId = $user->Id;
            $pending->Stage = $enum->label();
            $pending->ModifiedBy = $user->Id;
            $pending->ModifiedOn = Carbon::now();
            $pending->save();
        }

        activity()
            ->causedBy($user)
            ->performedOn($requisition)
            ->event(strtolower($action))
            ->log("{$action} inter-branch requisition (ID: {$requisition->Id}) with comment: '{$comments}'");
    }

    public function getApprovalLevelFromStatus($status)
    {
        return match ($status) {
            InterBranchRequisitionEnum::Submitted->value => InterBranchRequisitionEnum::Submitted->label(),
            InterBranchRequisitionEnum::Approved->value => InterBranchRequisitionEnum::Approved->label(),
            InterBranchRequisitionEnum::Rejected->value => InterBranchRequisitionEnum::Rejected->label(),
            default => 'N/A',
        };
    }
}
