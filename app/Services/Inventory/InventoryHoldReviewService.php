<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Branch;
use App\Models\Core\Approval\CodeDetail;
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
                throw new Exception("Cannot dispose item without a defect reason.");
            }

            InventoryHoldReview::create([
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

            $hold->update([
                'Status' => Transfers::Disposed->value,
                'DeletedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            $hold->delete();
        });
    }

    public function returnToSender(int $id, array $extras = []): void
    {
        DB::transaction(function () use ($id, $extras) {
            $hold = InventoryHold::findOrFail($id);

            $source = CodeDetail::find($hold->Source);
            if (!$source || $source->Value !== 'Tr') {
                throw new Exception("Return is only applicable for Transfer Receipt sources.");
            }

            $receipt = TransactionReceipt::find($hold->SourceID);
            if (!$receipt) {
                throw new Exception("No Transaction Receipt found for InventoryHold ID {$id}");
            }

            $transfer = TransactionTransfer::find($receipt->TransferId);
            if (!$transfer) {
                throw new Exception("Original transfer not found for Receipt ID {$receipt->Id}");
            }

            $fromBranch = $hold->BranchID;
            $toBranch   = $transfer->FromBranch;

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

            $receiptItems = TransactionReceiptItem::where('ReceiptId', $receipt->Id)
                ->where('Item', $hold->ItemID)
                ->get();

            foreach ($receiptItems as $item) {
                TransactionTransferItem::create([
                    'TransferId'     => $newTransfer->Id,
                    'Item'           => $item->Item,
                    'ApprovedQty'    => $hold->Quantity,
                    'DispatchedQty'  => $hold->Quantity,
                    'UnitCost'       => $item->UnitCost ?? 0,
                    'UOM'            => $item->UOM,
                    'Remarks'        => 'Returned from Inventory Hold ID ' . $hold->Id,
                    'CreatedBy'      => Auth::id(),
                    'ModifiedBy'     => Auth::id(),
                    'CreatedOn'      => now(),
                    'ModifiedOn'     => now(),
                ]);
            }

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

            $hold->update([
                'FromBranch' => $fromBranch,
                'BranchID'   => $toBranch,
                'Status'     => Transfers::Returned->value,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

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

        StockTransaction::create([
            'SKUID'           => $skuId,
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
    }
}
