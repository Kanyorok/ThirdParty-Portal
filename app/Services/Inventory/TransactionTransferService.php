<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Branch;
use App\Models\Core\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Procurement\GoodsReceipt;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory\StockTransaction;

use Throwable;

class TransactionTransferService
{
public function getHQBranchId(): int
    {
        $hqBranch = Branch::where('IsHQ', 1)->first();
        if (!$hqBranch) {
            throw new Exception('No HQ branch defined. Please set a branch as HQ.');
        }
        return $hqBranch->Id;
    }

   public function createTransfer(array $data): TransactionTransfer
{
    $data['Status'] = Transfers::Pending;

    if ($data['RequisitionType'] === 'procurement') {
        $requisition = GoodsReceipt::findOrFail($data['RequisitionId']);
        $fromBranch = $this->getHQBranchId();
        // FIX: Use the ToBranch from form data, not from requisition
        $toBranch = $data['ToBranch']; // This should be the branch ID from the dropdown
    } else {
        $requisition = InterBranchRequisition::findOrFail($data['RequisitionId']);
        $fromBranch = $requisition->FromBranch;
        $toBranch = $requisition->ToBranch;
    }

    // Debug: Check what values we're getting
    Log::info('Transfer creation data:', [
        'RequisitionType' => $data['RequisitionType'],
        'FromBranch' => $fromBranch,
        'ToBranch' => $toBranch,
        'Form_ToBranch' => $data['ToBranch'] ?? 'NOT SET'
    ]);

    if (empty($data['TransferDate'])) {
        throw new Exception('TransferDate is required.');
    }

    $transfer = new TransactionTransfer([
        'TransferDate' => $data['TransferDate'],
        'TransferredBy' => $data['TransferredBy'],
        'RequisitionId' => $data['RequisitionId'],
        'FromBranch' => $fromBranch,
        'ToBranch' => $toBranch,
        'RequisitionType' => $data['RequisitionType'],
        'Status' => $data['Status'],
        'CreatedBy' => Auth::id(),
        'ModifiedBy' => Auth::id(),
        'CreatedOn' => now(),
        'ModifiedOn' => now(),
    ]);
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

        activity()->performedOn($transfer)->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Created Transaction Transfer');

        return $transfer;
    }

    public function createTransferItems(TransactionTransfer $transfer, array $items): void
    {
        foreach ($items as $itemData) {

            $itemId = $itemData['item'];
            $dispatchedQty = $itemData['dispatched_qty'];

            $fromBranch = $transfer->RequisitionType === 'procurement'
                ? $this->getHQBranchId()
                : $transfer->FromBranch;

            // $stock = StockItem::where('ItemID', $itemId)
            //     ->where('Branch', $fromBranch)
            //     ->first();

            // if (!$stock || $stock->CurrentQty < $dispatchedQty) {
            //     throw new Exception("Insufficient stock for ItemID {$itemId} in Branch {$fromBranch}.");
            // }



            $created = TransactionTransferItem::create([
                'TransferId' => $transfer->Id,
                'Item' => $itemId,
                'ApprovedQty' => $itemData['approved_qty'],
                'UnitCost' => $itemData['unit_cost'] ?? null,
                'UOM' => $itemData['uom'],
                'DispatchedQty' => $dispatchedQty,
                'Remarks' => $itemData['remarks'] ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);

            activity()->performedOn($created)->causedBy(Auth::user())
                ->withProperties(['attributes' => $itemData])
                ->log('Created Transaction Transfer Item');
        }
    }

    public function update(TransactionTransfer $transfer, array $data): void
{
    DB::transaction(function () use ($transfer, $data) {

        // Update main transfer fields
        $transfer->TransferDate = $data['TransferDate'] ?? $transfer->TransferDate;
        $transfer->TransferredBy = $data['TransferredBy'] ?? $transfer->TransferredBy;
        
        // FIX: Update ToBranch if it's provided in the data
        // Only allow ToBranch update for procurement transfers
        if ($transfer->RequisitionType === 'procurement' && isset($data['ToBranch'])) {
            $transfer->ToBranch = $data['ToBranch'];
        }
        
        $transfer->ModifiedBy = auth()->id();
        $transfer->ModifiedOn = now();
        $transfer->save();

        // Update transfer items
        if (!empty($data['items'])) {

            foreach ($data['items'] as $itemData) {

                $transferItem = $transfer->items()->where('Item', $itemData['item'])->first();

                if ($transferItem) {
                    $transferItem->ApprovedQty = $itemData['approved_qty'];
                    $transferItem->DispatchedQty = $itemData['dispatched_qty'];
                    $transferItem->Remarks = $itemData['remarks'] ?? null;
                    $transferItem->UnitCost = $itemData['unit_cost'] ?? $transferItem->UnitCost;
                    $transferItem->ModifiedBy = auth()->id();
                    $transferItem->ModifiedOn = now();
                    $transferItem->save();

                    activity()->performedOn($transferItem)
                        ->causedBy(auth()->user())
                        ->withProperties(['attributes' => $itemData])
                        ->log('Updated Transaction Transfer Item');
                }
            }
        }

        activity()->performedOn($transfer)
            ->causedBy(auth()->user())
            ->withProperties(['attributes' => $data])
            ->log('Updated Transaction Transfer');
    });
}
       public function approve(int $id): void
{
    DB::beginTransaction();

    try {
        $transfer = TransactionTransfer::with('items')->findOrFail($id);
        $transfer->Status = Transfers::InTransit;
        $transfer->ModifiedBy = Auth::id();
        $transfer->ModifiedOn = now();
        $transfer->save();

        foreach ($transfer->items as $item) {
            $stockFrom = StockItem::where('ItemID', $item->Item)
                ->where('Branch', $transfer->FromBranch)
                ->first();

            if (!$stockFrom) {
                throw new Exception("No stock found for Item {$item->Item} in branch {$transfer->FromBranch}");
            }

            // ✅ STEP 1: Get last known balance BEFORE updating stock
            $lastBalance = StockTransaction::where('ItemID', $item->Item)
                ->where('BranchID', $transfer->FromBranch)
                ->orderByDesc('TransactionDate')
                ->orderByDesc('id')
                ->value('BalanceQty');

            if ($lastBalance === null) {
                $lastBalance = $stockFrom->CurrentQty ?? 0;
            }

            $dispatchedQty = $item->DispatchedQty;
            $newBalance = $lastBalance - $dispatchedQty; // new balance after dispatch
            $totalCost = ($item->UnitCost ?? 0) * $dispatchedQty * -1; // negative for stock out

            // ✅ STEP 2: Record StockTransaction BEFORE modifying StockItem
            $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                ->orderByDesc('id')
                ->value('SKUID');

            $nextNumber = $latestSKU
                ? str_pad(((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1, 3, '0', STR_PAD_LEFT)
                : '001';

            $skuId = 'SKU' . $nextNumber;

            StockTransaction::create([
                'SKUID' => $skuId,
                'TransactionType' => CodeDetail::where('CodeID', 'Source')
                    ->where('Description', 'Transaction Transfer')->value('ID'),
                'ItemID' => $item->Item,
                'StoreID' => $stockFrom->Store ?? null,
                'BranchID' => $transfer->FromBranch,
                'UnitCost' => $item->UnitCost,
                'UOMID' => $item->uom->Id ?? null,
                'QuantityIn' => 0,
                'QuantityOut' => $dispatchedQty,
                'BalanceQty' => $newBalance, // ✅ accurate, reflects pre-update balance - qty out
                'TotalCost' => $totalCost,
                'TransactionDate' => now(),
                'ReferenceID' => $transfer->Id,
                'Remarks' => 'Transfer to Branch ID ' . $transfer->ToBranch,
                'CreatedBy' => Auth::id(),
                'CreatedDate' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // ✅ STEP 3: Now update the actual stock quantity
            $stockFrom->CurrentQty = $newBalance;
            $stockFrom->ModifiedBy = Auth::id();
            $stockFrom->ModifiedOn = now();
            $stockFrom->save();

            // ✅ STEP 4: Log hold record for destination
            InventoryHold::create([
                'ItemID' => $item->Item,
                'BranchID' => $transfer->ToBranch,
                'Quantity' => $dispatchedQty,
                'Reason' => CodeDetail::where('CodeID', 'AdjustmentReason')
                    ->where('Description', 'In Transit')->value('ID'),
                'Source' => CodeDetail::where('CodeID', 'Source')
                    ->where('Description', 'Transaction Transfer')->value('ID'),
                'SourceID' => $transfer->Id,
                'Status' => Transfers::InTransit->value,
                'Remarks' => $item->Remarks,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);
        }

        // ✅ Workflow updates remain unchanged
        Workflow::create([
            'Source' => 'TransactionTransfer',
            'SourceID' => $transfer->Id,
            'Stage' => Transfers::InTransit->label(),
            'Status' => Transfers::InTransit->value,
            'Notes' => 'Transaction Transfer Approved: stock deducted from origin branch',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::where('Source', 'TransactionTransfer')
            ->where('SourceID', $transfer->Id)
            ->update(['Stage' => Transfers::InTransit->label()]);

        activity()->performedOn($transfer)->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Approved Transaction Transfer: stock deducted and transaction recorded');

        DB::commit();
    } catch (Throwable $th) {
        DB::rollBack();
        Log::error('Transfer approval failed: ' . $th->getMessage(), [
            'transfer_id' => $id,
            'user_id' => Auth::id(),
            'exception' => $th,
        ]);
        throw $th;
    }
}

    public function reject(int $id): void
    {
        $transfer = TransactionTransfer::findOrFail($id);
        $transfer->Status = Transfers::Rejected;
        $transfer->ModifiedBy = Auth::id();
        $transfer->ModifiedOn = now();
        $transfer->save();

        Workflow::create([
            'Source' => 'TransactionTransfer',
            'SourceID' => $transfer->Id,
            'Stage' => Transfers::Rejected->label(),
            'Status' => Transfers::Rejected->value,
            'Notes' => 'Transaction Transfer Rejected',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::where('Source', 'TransactionTransfer')
            ->where('SourceID', $transfer->Id)
            ->update(['Stage' => Transfers::Rejected->label()]);

        activity()->performedOn($transfer)->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Rejected Transaction Transfer');
    }

    protected function generateTransferId(TransactionTransfer $transfer): string
    {
        $year = now()->format('Y');
        return 'TRF-' . $year . '-' . str_pad($transfer->Id, 4, '0', STR_PAD_LEFT);
    }


    public function getApprovedTransfers()
    {
        return TransactionTransfer::where('Status', Transfers::InTransit)
            ->orderByDesc('CreatedOn')
            ->get([
                'Id', 
                'TransferId', 
                'TransferDate',
                'FromBranch',
                'ToBranch'
            ]);
    }


}
