<?php

namespace App\Services\Inventory;

use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Enums\Inventory\Transfers;
use App\Models\Inventory\StockItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionTransferService
{

    public function createTransfer(array $data): TransactionTransfer
    {
        $data['Status'] = Transfers::Pending;
        $requisition = InterBranchRequisition::findOrFail($data['RequisitionId']);

        if (empty($data['TransferDate'])) {
            throw new \Exception('TransferDate is required.');
        }

        $transfer = new TransactionTransfer();
        $transfer->TransferDate = $data['TransferDate'];
        $transfer->TransferredBy = $data['TransferredBy'];
        $transfer->RequisitionId = $data['RequisitionId'];
        $transfer->FromBranch = $requisition->FromBranch;
        $transfer->ToBranch = $requisition->ToBranch;
        $transfer->Status = $data['Status'];
        $transfer->CreatedBy = Auth::id();
        $transfer->ModifiedBy = Auth::id();
        $transfer->CreatedOn = Carbon::now();
        $transfer->ModifiedOn = Carbon::now();
        $transfer->save();

        $transfer->TransferId = $this->generateTransferId($transfer);
        $transfer->save();


        Workflow::create([
            'Source' => 'TransactionTransfer',
            'SourceID' => $transfer->Id,
            'Stage' => Transfers::Pending->label(),
            'Status' => Transfers::Pending->value,
            'Notes' => 'Transaction Transfers Pending',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);


        PendingWorkflow::updateOrCreate(
            ['Source' => 'TransactionTransfer', 'SourceID' => $transfer->Id],
            [
                'Stage' => Transfers::Pending->label(),
                'UserId' => Auth::id(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]
        );


        activity()
            ->performedOn($transfer)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Created Transaction Transfer');

        return $transfer;
    }

    protected function generateTransferId(TransactionTransfer $transfer): string
    {
        $year = now()->format('Y');
        return 'TRF-' . $year . '-' . str_pad($transfer->Id, 4, '0', STR_PAD_LEFT);
    }

    public function createTransferItems(TransactionTransfer $transfer, array $items): void
    {
        foreach ($items as $itemData) {
            $created = TransactionTransferItem::create([
                'TransferId' => $transfer->Id,
                'Item' => $itemData['item'],
                'ApprovedQty' => $itemData['approved_qty'],
                'DispatchedQty' => $itemData['dispatched_qty'],
                'Remarks' => $itemData['remarks'] ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => Carbon::now(),
                'ModifiedOn' => Carbon::now(),
            ]);

            activity()
                ->performedOn($created)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $itemData])
                ->log('Created Transaction Transfer Item');
        }
    }

    public function approve(int $transferId): void
    {
        DB::transaction(function () use ($transferId) {
            $transfer = TransactionTransfer::with('items')->findOrFail($transferId);
            $transfer->Status = Transfers::InTransit->value;
            $transfer->ModifiedBy = Auth::id();
            $transfer->ModifiedOn = now();
            $transfer->save();
            Log::debug("Transaction Transfer {$transfer->TransferId} approved. Status set to InTransit.");

            foreach ($transfer->items as $item) {
                Log::debug("Processing item {$item->Item} for deduction from FromBranch {$transfer->FromBranch}. DispatchedQty: {$item->DispatchedQty}");
                $fromStock = StockItem::where('ItemID', $item->Item)
                    ->where('Branch', $transfer->FromBranch)
                    ->first();

                if ($fromStock) {
                    Log::debug("Found FromBranch StockItem. CurrentQty before deduction: {$fromStock->CurrentQty}");
                    $fromStock->CurrentQty = max(0, $fromStock->CurrentQty - $item->DispatchedQty);
                    $fromStock->save();
                    Log::debug("FromBranch StockItem updated. New CurrentQty: {$fromStock->CurrentQty}");
                    activity()
                        ->causedBy(Auth::user())
                        ->performedOn($fromStock)
                        ->event('stock_deducted_for_transfer')
                        ->log("Stock deducted by {$item->DispatchedQty} for Item {$item->Item} in Branch {$transfer->FromBranch} due to transfer {$transfer->TransferId}.");
                } else {

                    \Log::warning("StockItem not found in FromBranch for deduction: ItemID={$item->Item}, Branch={$transfer->FromBranch}. Transfer ID: {$transfer->Id}. Deduction skipped.");
                }

                Log::debug("Processing item {$item->Item} for addition to ToBranch {$transfer->ToBranch}. DispatchedQty: {$item->DispatchedQty}");
                $toStock = StockItem::where('ItemID', $item->Item)
                    ->where('Branch', $transfer->ToBranch)
                    ->first();

                if (!$toStock) {
                    Log::debug("StockItem not found in ToBranch, creating new one for ItemID: {$item->Item}, Branch: {$transfer->ToBranch}");
                    $toStock = StockItem::create([
                        'ItemID' => $item->Item,
                        'Branch' => $transfer->ToBranch,
                        'CurrentQty' => 0,
                    ]);
                }

                Log::debug("ToBranch StockItem CurrentQty before addition: {$toStock->CurrentQty}");
                $toStock->CurrentQty += $item->DispatchedQty;
                $toStock->save();
                Log::debug("ToBranch StockItem updated. New CurrentQty: {$toStock->CurrentQty}");

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($toStock)
                    ->event('stock_added_for_transfer_in_transit')
                    ->log("Stock virtually added by {$item->DispatchedQty} for Item {$item->Item} in Branch {$transfer->ToBranch} (in transit) due to transfer {$transfer->TransferId}.");
            }

            Workflow::create([
                'Source' => 'TransactionTransfer',
                'SourceID' => $transfer->Id,
                'Stage' => Transfers::InTransit->label(),
                'Status' => Transfers::InTransit->value,
                'Notes' => 'Transfer approved and dispatched, now in transit',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);


            PendingWorkflow::where('Source', 'TransactionTransfer')
                ->where('SourceID', $transfer->Id)
                ->update(['Stage' => Transfers::InTransit->label()]);

            activity()
                ->performedOn($transfer)
                ->causedBy(Auth::user())
                ->withProperties(['status' => Transfers::InTransit])
                ->log("Approved Transaction Transfer {$transfer->TransferId}, now in transit.");
        });
    }

    public function update(TransactionTransfer $transfer, array $data): TransactionTransfer
    {
        DB::beginTransaction();

        try {
            $items = $data['items'] ?? [];
            unset($data['items']);
            $requisition = InterBranchRequisition::findOrFail($data['RequisitionId']);


            $transfer->fill($data);
            $transfer->TransferDate = $data['TransferDate'];
            $transfer->TransferredBy = $data['TransferredBy'];
            $transfer->RequisitionId = $data['RequisitionId'];
            $transfer->FromBranch = $requisition->FromBranch;
            $transfer->ToBranch = $requisition->ToBranch;
            $transfer->ModifiedBy = Auth::id();
            $transfer->ModifiedOn = Carbon::now();
            $transfer->save();


            foreach ($items as $itemData) {
                $item = TransactionTransferItem::where('TransferId', $transfer->Id)
                    ->where('Item', $itemData['item'])
                    ->first();

                if ($item) {
                    // Update existing item
                    $item->update([
                        'ApprovedQty' => $itemData['approved_qty'],
                        'DispatchedQty' => $itemData['dispatched_qty'],
                        'Remarks' => $itemData['remarks'] ?? null,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => Carbon::now(),
                    ]);
                } else {
                    // Optionally create new item if it doesn't exist
                    TransactionTransferItem::create([
                        'TransferId' => $transfer->Id,
                        'Item' => $itemData['item'],
                        'ApprovedQty' => $itemData['approved_qty'],
                        'DispatchedQty' => $itemData['dispatched_qty'],
                        'Remarks' => $itemData['remarks'] ?? null,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                        'CreatedOn' => Carbon::now(),
                        'ModifiedOn' => Carbon::now(),
                    ]);
                }
            }

            DB::commit();

            activity()
                ->performedOn($transfer)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data, 'items' => $items])
                ->log('Updated Transaction Transfer');

            return $transfer;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to update transaction transfer: ' . $th->getMessage());
            throw $th;
        }
    }

    public function delete(TransactionTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            Log::debug("Attempting to delete transfer {$transfer->Id} and revert stock.");
            if ($transfer->Status === Transfers::InTransit->value || $transfer->Status === Transfers::Delivered->value) {
                foreach ($transfer->items as $item) {
                    $fromStock = StockItem::where('ItemID', $item->Item)
                        ->where('Branch', $transfer->FromBranch)
                        ->first();
                    if ($fromStock) {
                        Log::debug("Reverting deduction for Item {$item->Item} at FromBranch {$transfer->FromBranch}. CurrentQty before: {$fromStock->CurrentQty}");
                        $fromStock->CurrentQty += $item->DispatchedQty;
                        $fromStock->save();
                        Log::debug("FromBranch StockItem reverted. New CurrentQty: {$fromStock->CurrentQty}");
                        activity()
                            ->causedBy(Auth::user())
                            ->performedOn($fromStock)
                            ->event('stock_reverted_on_transfer_delete')
                            ->log("Stock addition of {$item->DispatchedQty} reverted for Item {$item->Item} in Branch {$transfer->FromBranch} due to transfer deletion {$transfer->TransferId}.");
                    } else {
                        \Log::warning("FromBranch StockItem not found during transfer deletion reversion: ItemID={$item->Item}, Branch={$transfer->FromBranch}. Transfer ID: {$transfer->Id}");
                    }

                    $toStock = StockItem::where('ItemID', $item->Item)
                        ->where('Branch', $transfer->ToBranch)
                        ->first();
                    if ($toStock) {
                        Log::debug("Reverting addition for Item {$item->Item} at ToBranch {$transfer->ToBranch}. CurrentQty before: {$toStock->CurrentQty}");
                        $toStock->CurrentQty = max(0, $toStock->CurrentQty - $item->DispatchedQty);
                        $toStock->save();
                        Log::debug("ToBranch StockItem reverted. New CurrentQty: {$toStock->CurrentQty}");
                        activity()
                            ->causedBy(Auth::user())
                            ->performedOn($toStock)
                            ->event('stock_reverted_on_transfer_delete')
                            ->log("Stock deduction of {$item->DispatchedQty} reverted for Item {$item->Item} in Branch {$transfer->ToBranch} due to transfer deletion {$transfer->TransferId}.");
                    } else {
                        \Log::warning("ToBranch StockItem not found during transfer deletion reversion: ItemID={$item->Item}, Branch={$transfer->ToBranch}. Transfer ID: {$transfer->Id}");
                    }
                }
            } else {
                Log::debug("Transfer {$transfer->Id} was not in 'InTransit' or 'Delivered' status. No stock reversion performed.");
            }

            Workflow::where('Source', 'TransactionTransfer')->where('SourceID', $transfer->Id)->delete();
            PendingWorkflow::where('Source', 'TransactionTransfer')->where('SourceID', $transfer->Id)->delete();

            activity()
                ->performedOn($transfer)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $transfer->toArray()])
                ->log("Transaction Transfer {$transfer->TransferId} deleted.");

            $transfer->delete();
        });
    }

    public function reject(int $transferId): void
    {
        DB::transaction(function () use ($transferId) {
            $transfer = TransactionTransfer::findOrFail($transferId);

            if (in_array($transfer->Status, [Transfers::Delivered->value])) {
                throw new \Exception("Cannot reject a delivered transfer.");
            }

            $transfer->Status = Transfers::Rejected->value;
            $transfer->ModifiedBy = Auth::id();
            $transfer->ModifiedOn = now();
            $transfer->save();

            // Record the workflow step
            Workflow::create([
                'Source' => 'TransactionTransfer',
                'SourceID' => $transfer->Id,
                'Stage' => Transfers::Rejected->label(),
                'Status' => Transfers::Rejected->value,
                'Notes' => 'Transfer rejected.',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Update PendingWorkflow
            PendingWorkflow::where('Source', 'TransactionTransfer')
                ->where('SourceID', $transfer->Id)
                ->update(['Stage' => Transfers::Rejected->label()]);

            // Log the rejection
            activity()
                ->performedOn($transfer)
                ->causedBy(Auth::user())
                ->withProperties(['status' => Transfers::Rejected])
                ->log("Transaction Transfer {$transfer->TransferId} was rejected.");
        });
    }

}
