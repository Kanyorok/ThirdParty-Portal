<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Branch;
use App\Models\Core\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Inventory\TransactionReceiptItem;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\InventoryHoldReview;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\StockItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class InventoryHoldReviewService
{
    public function review(array $data): void
    {
        $id = $data['Id'] ?? $data['InventoryHoldID'] ?? null;
        if (!$id) {
            throw new InvalidArgumentException('InventoryHold Id is required.');
        }

        $hold = InventoryHold::findOrFail($id);

        Log::info('Reviewing Inventory Hold', ['id' => $hold->Id]);

        $hold->update([
            'Condition' => $data['Condition'],
            'Notes' => $data['Notes'],
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);
    }

    public function dispose(int $id, array $extras = []): void
    {
        DB::transaction(function () use ($id, $extras) {
            $hold = InventoryHold::with('item')->findOrFail($id);

            if (!$hold->Reason) {
                Log::error("Disposal blocked: Missing reason for InventoryHold ID {$id}");
                throw new Exception("Cannot dispose item without a defect reason.");
            }

            $review = InventoryHoldReview::create([
                'InventoryHoldID' => $hold->Id,
                'ItemID' => $hold->ItemID,
                'FromBranch' => $hold->BranchID,
                'Store' => $hold->Store,
                'Quantity' => $hold->Quantity,
                'Defect' => $hold->Reason,
                'Condition' => $extras['Condition'] ?? null,
                'Notes' => $extras['Notes'] ?? null,
                'Status' => Transfers::Disposed->value,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            Log::info("Disposal record created", ['review_id' => $review->Id]);

            $hold->update([
                'Status' => Transfers::Disposed->value,
                'DeletedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            $hold->delete();

            Log::info("InventoryHold marked as disposed and soft-deleted", ['id' => $hold->Id]);
        });
    }

    public function returnToSender(int $id, array $extras = []): void
    {
        DB::transaction(function () use ($id, $extras) {
            $hold = InventoryHold::findOrFail($id);

            // Validate source (must be a Transfer Receipt)
            $source = CodeDetail::find($hold->Source);
            if (!$source || $source->Value !== 'Tr') {
                throw new Exception("Return is only applicable for Transfer Receipt sources.");
            }

            // Locate original receipt and transfer
            $receipt = TransactionReceipt::find($hold->SourceID);
            if (!$receipt) {
                throw new Exception("No Transaction Receipt found for InventoryHold ID {$id}");
            }

            $transfer = TransactionTransfer::find($receipt->TransferId);
            if (!$transfer) {
                throw new Exception("Original transfer not found for Receipt ID {$receipt->Id}");
            }

            $fromBranch = $hold->BranchID;          // current branch (sending back)
            $toBranch   = $transfer->FromBranch;    // original sender (receiving back)

            // Create Return Transfer
            $newTransfer = TransactionTransfer::create([
                'TransferDate'    => now(),
                'TransferredBy'   => Auth::id(),
                'RequisitionId'   => $transfer->RequisitionId,
                'FromBranch'      => $fromBranch,
                'ToBranch'        => $toBranch,
                'RequisitionType' => 'return',
                'Status'          => Transfers::InTransit->value,
                'CreatedBy'       => Auth::id(),
                'ModifiedBy'      => Auth::id(),
                'CreatedOn'       => now(),
                'ModifiedOn'      => now(),
            ]);

            $newTransfer->TransferId = 'RTR-' . now()->format('Y') . '-' . str_pad($newTransfer->Id, 4, '0', STR_PAD_LEFT);
            $newTransfer->save();

            Log::info('✅ Created Return Transfer', ['transfer_id' => $newTransfer->Id]);

            // Clone only the damaged quantity
            $receiptItems = TransactionReceiptItem::where('ReceiptId', $receipt->Id)
                ->where('Item', $hold->ItemID)
                ->get();

            foreach ($receiptItems as $item) {
                $transferItem = TransactionTransferItem::create([
                    'TransferId'     => $newTransfer->Id,
                    'Item'           => $item->Item,
                    'ApprovedQty'    => $hold->Quantity, // only damaged qty
                    'DispatchedQty'  => $hold->Quantity,
                    'UnitCost'       => $item->UnitCost ?? 0,
                    'UOM'            => $item->UOM,
                    'Remarks'        => 'Returned from Inventory Hold ID ' . $hold->Id,
                    'CreatedBy'      => Auth::id(),
                    'ModifiedBy'     => Auth::id(),
                    'CreatedOn'      => now(),
                    'ModifiedOn'     => now(),
                ]);

                Log::info('↩️ Created Return Transfer Item', ['item_id' => $transferItem->Id]);
            }

            // Workflow setup
            Workflow::create([
                'Source'     => 'TransactionTransfer',
                'SourceID'   => $newTransfer->Id,
                'Stage'      => Transfers::InTransit->label(),
                'Status'     => Transfers::InTransit->value,
                'Notes'      => 'Return transfer initiated from InventoryHold #' . $hold->Id,
                'CreatedBy'  => Auth::id(),
                'CreatedOn'  => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            PendingWorkflow::updateOrCreate(
                ['Source' => 'TransactionTransfer', 'SourceID' => $newTransfer->Id],
                [
                    'Stage'      => Transfers::InTransit->label(),
                    'UserId'     => Auth::id(),
                    'CreatedBy'  => Auth::id(),
                    'CreatedOn'  => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]
            );

            // Log in InventoryHoldReview (t_Defects)
            InventoryHoldReview::create([
                'InventoryHoldID' => $hold->Id,
                'ItemID'          => $hold->ItemID,
                'FromBranch'      => $fromBranch,
                'Store'           => $hold->Store,
                'Quantity'        => $hold->Quantity,
                'Defect'          => $hold->Reason ?? 'Returned Item',
                'Condition'       => $extras['Condition'] ?? null,
                'Notes'           => 'Returned to original sender via Transfer #' . $newTransfer->TransferId,
                'Status'          => Transfers::Returned->value,
                'CreatedBy'       => Auth::id(),
                'CreatedOn'       => now(),
                'ModifiedBy'      => Auth::id(),
                'ModifiedOn'      => now(),
            ]);

            Log::info("Return recorded in InventoryHoldReview", [
                'hold_id' => $hold->Id,
                'status'  => Transfers::Returned->value
            ]);

            // Update InventoryHold
            $hold->update([
                'FromBranch' => $fromBranch,
                'BranchID'   => $toBranch,
                'Status'     => Transfers::Returned->value,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Create Stock Transaction entry (deduct damaged qty)
            $this->recordStockTransaction(
                $hold->ItemID,
                $hold->Store,
                $fromBranch,
                $receiptItems->first()->UnitCost ?? 0,
                $receiptItems->first()->UOM ?? null,
                $hold->Quantity,
                'Returned damaged item to Branch ID ' . $toBranch . ' via Transfer #' . $newTransfer->TransferId,
                'Transaction Transfer',
                $newTransfer->Id
            );

            activity()->performedOn($newTransfer)
                ->causedBy(Auth::user())
                ->withProperties([
                    'attributes' => [
                        'HoldID'     => $hold->Id,
                        'TransferID' => $newTransfer->Id,
                        'FromBranch' => $fromBranch,
                        'ToBranch'   => $toBranch
                    ]
                ])
                ->log('Created Return Transfer from Inventory Hold');
        });
    }

      private function recordStockTransaction(
    $itemId,
    $storeId,
    $branchId,
    $unitCost,
    $uomId,
    $qtyOut,
    $remarks,
    $sourceDescription,
    $referenceId = null
) {
    $skuRecord = StockItem::where('ItemID', $itemId)
        ->where('Branch', $branchId)
        ->where('Store', $storeId)
        ->first();

    if (!$skuRecord) {
        Log::warning("⚠️ No StockItem record found for ItemID {$itemId} in Branch {$branchId}, Store {$storeId}");
        return;
    }

    $nextId = (StockTransaction::max('Id') ?? 0) + 1;
    $skuId = 'STX-' . now()->format('Y') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

    $transactionType = CodeDetail::where('CodeID', 'Source')
        ->where('Description', $sourceDescription)
        ->value('ID');

    $lastBalance = StockTransaction::where('BranchID', $branchId)
        ->where('ItemID', $itemId)
        ->orderByDesc('Id')
        ->value('BalanceQty') ?? $skuRecord->CurrentQty ?? 0;

    $newBalance = max(0, $lastBalance - $qtyOut);

    $transaction = StockTransaction::create([
        'SKUID'           => $skuId, // unique per transaction
        'TransactionType' => $transactionType,
        'ReferenceID'     => $referenceId,
        'ItemID'          => $itemId,
        'StoreID'         => $storeId,
        'BranchID'        => $branchId,
        'UnitCost'        => $unitCost ?? 0,
        'UOMID'           => $uomId,
        'QuantityIn'      => 0,
        'QuantityOut'     => $qtyOut,
        'BalanceQty'      => $newBalance,
        'TransactionDate' => now(),
        'TotalCost'       => ($unitCost ?? 0) * $qtyOut,
        'Remarks'         => $remarks,
        'CreatedBy'       => Auth::id(),
        'CreatedOn'       => now(),
        'ModifiedBy'      => Auth::id(),
        'ModifiedOn'      => now(),
    ]);

    $skuRecord->update([
        'CurrentQty'  => $newBalance,
        'ModifiedBy'  => Auth::id(),
        'ModifiedOn'  => now(),
    ]);

    Log::info('📉 New Stock Transaction created & stock updated', [
        'transaction_sku' => $transaction->SKUID,
        'item_id'         => $itemId,
        'branch'          => $branchId,
        'store'           => $storeId,
        'deducted_qty'    => $qtyOut,
        'new_balance'     => $newBalance,
    ]);
}


}
