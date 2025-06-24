<?php

namespace App\Services\Inventory;

use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use App\Models\Inventory\StockItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Enums\Inventory\Transfers;
use App\Models\Core\Branch;

class TransactionTransferService
{
    protected function getHQBranchId(): int
    {
        $hqBranch = Branch::where('IsHQ', 1)->first();
        if (!$hqBranch) {
            throw new \Exception('No HQ branch defined. Please set a branch as HQ.');
        }
        return $hqBranch->Id;
    }

    public function createTransfer(array $data): TransactionTransfer
    {
        $data['Status'] = Transfers::Pending;

        if ($data['RequisitionType'] === 'procurement') {
            $requisition = \App\Models\Procurement\Requisitions::findOrFail($data['RequisitionId']);
            $fromBranch = $this->getHQBranchId();
            $toBranch = $requisition->BranchID;
        } else {
            $requisition = InterBranchRequisition::findOrFail($data['RequisitionId']);
            $fromBranch = $requisition->FromBranch;
            $toBranch = $requisition->ToBranch;
        }

        if (empty($data['TransferDate'])) {
            throw new \Exception('TransferDate is required.');
        }

        $transfer = new TransactionTransfer([
            'TransferDate'     => $data['TransferDate'],
            'TransferredBy'    => $data['TransferredBy'],
            'RequisitionId'    => $data['RequisitionId'],
            'FromBranch'       => $fromBranch,
            'ToBranch'         => $toBranch,
            'RequisitionType'  => $data['RequisitionType'],
            'Status'           => $data['Status'],
            'CreatedBy'        => Auth::id(),
            'ModifiedBy'       => Auth::id(),
            'CreatedOn'        => now(),
            'ModifiedOn'       => now(),
        ]);
        $transfer->save();

        $transfer->TransferId = $this->generateTransferId($transfer);
        $transfer->save();

        Workflow::create([
            'Source'     => 'TransactionTransfer',
            'SourceID'   => $transfer->Id,
            'Stage'      => Transfers::Pending->label(),
            'Status'     => Transfers::Pending->value,
            'Notes'      => 'Transaction Transfers Pending',
            'CreatedBy'  => Auth::id(),
            'CreatedOn'  => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::updateOrCreate(
            ['Source' => 'TransactionTransfer', 'SourceID' => $transfer->Id],
            [
                'Stage'      => Transfers::Pending->label(),
                'UserId'     => Auth::id(),
                'CreatedBy'  => Auth::id(),
                'CreatedOn'  => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]
        );

        activity()->performedOn($transfer)->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Created Transaction Transfer');

        return $transfer;
    }

    public function createTransferItems(TransactionTransfer $transfer, array $items): void
    {
        foreach ($items as $itemData) {
            $created = TransactionTransferItem::create([
                'TransferId'    => $transfer->Id,
                'Item'          => $itemData['item'],
                'ApprovedQty'   => $itemData['approved_qty'],
                'UOM'           => $itemData['uom'],
                'DispatchedQty' => $itemData['dispatched_qty'],
                'Remarks'       => $itemData['remarks'] ?? null,
                'CreatedBy'     => Auth::id(),
                'ModifiedBy'    => Auth::id(),
                'CreatedOn'     => now(),
                'ModifiedOn'    => now(),
            ]);

            activity()->performedOn($created)->causedBy(Auth::user())
                ->withProperties(['attributes' => $itemData])
                ->log('Created Transaction Transfer Item');
        }
    }

    public function update(TransactionTransfer $transfer, array $data): TransactionTransfer
    {
        DB::beginTransaction();

        try {
            $items = $data['items'] ?? [];
            unset($data['items']);

            if ($data['RequisitionType'] === 'procurement') {
                $requisition = \App\Models\Procurement\Requisitions::findOrFail($data['RequisitionId']);
                $fromBranch = $this->getHQBranchId();
                $toBranch = $requisition->BranchID;
            } else {
                $requisition = InterBranchRequisition::findOrFail($data['RequisitionId']);
                $fromBranch = $requisition->FromBranch;
                $toBranch = $requisition->ToBranch;
            }

            $transfer->fill([
                'TransferDate'     => $data['TransferDate'],
                'TransferredBy'    => $data['TransferredBy'],
                'RequisitionId'    => $data['RequisitionId'],
                'RequisitionType'  => $data['RequisitionType'],
                'FromBranch'       => $fromBranch,
                'ToBranch'         => $toBranch,
                'ModifiedBy'       => Auth::id(),
                'ModifiedOn'       => now(),
            ]);
            $transfer->save();

            foreach ($items as $itemData) {
                TransactionTransferItem::updateOrCreate(
                    [
                        'TransferId' => $transfer->Id,
                        'Item'       => $itemData['item'],
                    ],
                    [
                        'ApprovedQty'   => $itemData['approved_qty'],
                        'DispatchedQty' => $itemData['dispatched_qty'],
                        'UOM'           => $itemData['uom'],
                        'Remarks'       => $itemData['remarks'] ?? null,
                        'ModifiedBy'    => Auth::id(),
                        'ModifiedOn'    => now(),
                    ]
                );
            }

            DB::commit();

            activity()->performedOn($transfer)->causedBy(Auth::user())
                ->withProperties(['attributes' => $data, 'items' => $items])
                ->log('Updated Transaction Transfer');

            return $transfer;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to update transaction transfer: ' . $th->getMessage());
            throw $th;
        }
    }

    public function approve(int $id): void
    {
        $transfer = TransactionTransfer::findOrFail($id);
        $transfer->Status = Transfers::Approved;
        $transfer->ModifiedBy = Auth::id();
        $transfer->ModifiedOn = now();
        $transfer->save();

        Workflow::create([
            'Source'     => 'TransactionTransfer',
            'SourceID'   => $transfer->Id,
            'Stage'      => Transfers::Approved->label(),
            'Status'     => Transfers::Approved->value,
            'Notes'      => 'Transaction Transfer Approved',
            'CreatedBy'  => Auth::id(),
            'CreatedOn'  => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::where('Source', 'TransactionTransfer')
            ->where('SourceID', $transfer->Id)
            ->delete();

        activity()->performedOn($transfer)->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Approved Transaction Transfer');
    }

    public function reject(int $id): void
    {
        $transfer = TransactionTransfer::findOrFail($id);
        $transfer->Status = Transfers::Rejected;
        $transfer->ModifiedBy = Auth::id();
        $transfer->ModifiedOn = now();
        $transfer->save();

        Workflow::create([
            'Source'     => 'TransactionTransfer',
            'SourceID'   => $transfer->Id,
            'Stage'      => Transfers::Rejected->label(),
            'Status'     => Transfers::Rejected->value,
            'Notes'      => 'Transaction Transfer Rejected',
            'CreatedBy'  => Auth::id(),
            'CreatedOn'  => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::where('Source', 'TransactionTransfer')
            ->where('SourceID', $transfer->Id)
            ->delete();

        activity()->performedOn($transfer)->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Rejected Transaction Transfer');
    }

    protected function generateTransferId(TransactionTransfer $transfer): string
    {
        $year = now()->format('Y');
        return 'TRF-' . $year . '-' . str_pad($transfer->Id, 4, '0', STR_PAD_LEFT);
    }
}
