<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockGRNLedger;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\Store;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\TransactionTransfer;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionReceiptService
{
    public function createReceipt($validatedData, $items)
    {
        return DB::transaction(function () use ($validatedData, $items) {
            $userId = Auth::id();
            if (! $userId) {
                throw new Exception('User not authenticated. Cannot create receipt.');
            }

            $transfer = TransactionTransfer::with(['items.item'])->findOrFail($validatedData['TransferID']);

            $deliveredValue = Transfers::Delivered->value;
            $inTransitValue = Transfers::InTransit->value;

            $receipt = TransactionReceipt::create([
                'TransferId' => $validatedData['TransferID'],
                'ReceivedBy' => $validatedData['ReceivedBy'],
                'ReceivedDate' => $validatedData['ReceivedDate'],
                'GeneralRemarks' => $validatedData['GeneralRemarks'] ?? null,
                'Status' => $deliveredValue,
                'CreatedBy' => $userId,
                'CreatedOn' => now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);

            $receipt->ReceiptId = 'REC/' . now()->format('Ymd') . '/' . str_pad($receipt->Id, 4, '0', STR_PAD_LEFT);
            $receipt->save();

            if ($transfer && $transfer->Status == $inTransitValue) {
                $transfer->Status = $deliveredValue;
                $transfer->ModifiedBy = $userId;
                $transfer->ModifiedOn = now();
                $transfer->save();
            }

            $this->createReceiptItemsWithFIFO($receipt, $items, $transfer, $userId);

            $sourceCodeId = CodeDetail::where('CodeID', 'Source')
                ->where('Description', 'Transaction Transfer')
                ->value('ID');

            if ($sourceCodeId) {
                InventoryHold::where('Source', $sourceCodeId)
                    ->where('SourceID', $receipt->TransferId)
                    ->where('Status', $inTransitValue)
                    ->whereNull('DeletedOn')
                    ->update([
                        'Status' => $deliveredValue,
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => now(),
                        'DeletedBy' => $userId,
                        'DeletedOn' => now(),
                    ]);
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($receipt)
                ->withProperties(['attributes' => $receipt->toArray()])
                ->log('Transaction Receipt created and marked as Delivered');

            return $receipt;
        });
    }

    public function createReceiptItemsWithFIFO($receipt, $items, $transfer, $userId = null)
    {
        if (! $userId) {
            $userId = Auth::id();
            if (! $userId) {
                throw new Exception('User not authenticated. Cannot create receipt items.');
            }
        }

        $toBranchId = $receipt->transfer->ToBranch;

        $transferSource = CodeDetail::where('CodeID', 'Source')
            ->where('Description', 'Transfer Receipts')
            ->value('ID');

        if (! $transferSource) {
            throw new Exception("Source type 'Transfer Receipts' not found in t_CodeDetails.");
        }

        $itemsByStore = collect($items)->groupBy('store_id');

        foreach ($itemsByStore as $storeId => $storeItems) {
            foreach ($storeItems as $itemData) {
                $itemId = $itemData['item'];
                $receivedQty = $itemData['received_qty'];
                $damagedQty = $itemData['damaged_qty'] ?? 0;

                $transferItem = $transfer->items()->where('Item', $itemId)->first();

                if (! $transferItem) {
                    throw new Exception("Transfer item not found for item {$itemId}");
                }

                $stock = StockItem::where('ItemID', $itemId)
                    ->where('Branch', $toBranchId)
                    ->where('Store', $storeId)
                    ->first();

                $averageCost = $this->calculateAverageCost($transferItem, $receivedQty);

                if (! $stock) {
                    $stock = StockItem::create([
                        'SKUCode' => 'SKU-' . $itemId . '-' . $storeId . '-' . time(),
                        'ItemID' => $itemId,
                        'UOM' => $itemData['uom'] ?? null,
                        'UnitCost' => $averageCost,
                        'Store' => $storeId,
                        'Branch' => $toBranchId,
                        'CurrentQty' => 0,
                        'Min' => 0,
                        'Reorder' => 0,
                        'Max' => 0,
                        'LastReceived' => now(),
                        'Status' => true,
                        'CreatedBy' => $userId,
                        'CreatedOn' => now(),
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => now(),
                    ]);
                } else {
                    $oldQty = $stock->CurrentQty;
                    $oldCost = $stock->UnitCost;
                    $newQty = $receivedQty;

                    if ($oldQty > 0) {
                        $stock->UnitCost = (($oldQty * $oldCost) + ($newQty * $averageCost)) / ($oldQty + $newQty);
                    } else {
                        $stock->UnitCost = $averageCost;
                    }
                }

                $receiptItem = $receipt->items()->create([
                    'item' => $itemId,
                    'Store' => $storeId,
                    'UnitCost' => $averageCost,
                    'UOM' => $itemData['uom'],
                    'ReceivedQty' => $receivedQty,
                    'DispatchedQty' => $itemData['dispatched_qty'] ?? null,
                    'Discrepancy' => isset($itemData['dispatched_qty'], $itemData['received_qty'])
                        ? $itemData['dispatched_qty'] - $itemData['received_qty']
                        : null,
                    'DamagedQty' => $damagedQty,
                    'CreatedBy' => $userId,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => now(),
                ]);

                $stock->CurrentQty += $receivedQty;
                $stock->ModifiedBy = $userId;
                $stock->ModifiedOn = now();
                $stock->save();

                $this->createGRNLedgerEntriesForTransfer($receipt, $transferItem, $stock, $storeId, $toBranchId, $receivedQty, $userId);

                $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                    ->orderByDesc('id')
                    ->value('SKUID');

                $nextNumber = $latestSKU
                    ? ((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1
                    : 1;

                $skuId = 'SKU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                $lastToQty = StockTransaction::where('ItemID', $itemId)
                    ->where('BranchID', $toBranchId)
                    ->where('StoreID', $storeId)
                    ->orderByDesc('TransactionDate')
                    ->orderByDesc('id')
                    ->value('BalanceQty');

                if ($lastToQty === null) {
                    $lastToQty = $stock->CurrentQty - $receivedQty;
                }

                $newToQty = $lastToQty + $receivedQty;

                StockTransaction::create([
                    'SKUID' => $skuId,
                    'TransactionType' => $transferSource,
                    'ReferenceID' => $receipt->Id,
                    'ItemID' => $itemId,
                    'StoreID' => $storeId,
                    'BranchID' => $toBranchId,
                    'UnitCost' => $averageCost,
                    'UOMID' => $itemData['uom'],
                    'QuantityIn' => $receivedQty,
                    'QuantityOut' => 0,
                    'BalanceQty' => $newToQty,
                    'TransactionDate' => now(),
                    'TotalCost' => $averageCost * $receivedQty,
                    'Remarks' => 'Transfer From Branch ID ' . ($transfer->FromBranch ?? 'Unknown') .
                                ' | Transfer ID: ' . $transfer->TransferId .
                                ' | Allocations: ' . $this->getAllocationSummary($transferItem),
                    'CreatedBy' => $userId,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => now(),
                ]);

                if ($damagedQty > 0) {
                    $damagedReasonId = CodeDetail::where('CodeID', 'AdjustmentReason')
                        ->where('Description', 'Damaged in Transit')
                        ->value('ID');

                    if (! $damagedReasonId) {
                        throw new Exception("Damaged in Transit reason not found in t_CodeDetails.");
                    }

                    InventoryHold::create([
                        'ItemID' => $itemId,
                        'BranchID' => $toBranchId,
                        'Store' => $storeId,
                        'Quantity' => $damagedQty,
                        'Reason' => $damagedReasonId,
                        'Source' => $transferSource,
                        'SourceID' => $receipt->Id,
                        'Status' => Transfers::AwaitingReview->value,
                        'Remarks' => $itemData['remarks'] ?? null,
                        'CreatedBy' => $userId,
                        'CreatedOn' => now(),
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => now(),
                    ]);
                }

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($receiptItem)
                    ->withProperties(['attributes' => $receiptItem->toArray()])
                    ->log('Receipt item added and stock updated with GRN allocations');
            }
        }
    }

    private function createGRNLedgerEntriesForTransfer($receipt, $transferItem, $stock, $storeId, $branchId, $receivedQty, $userId)
    {
        $batchAllocations = json_decode($transferItem->BatchAllocation, true) ?? [];

        if (empty($batchAllocations)) {
            $allocations = $this->allocateFIFOFromSource($transferItem, $storeId, $branchId, $receivedQty);
        } else {
            $allocations = $batchAllocations;
        }

        $totalAllocated = 0;
        foreach ($allocations as $allocation) {
            $totalAllocated += $allocation['quantity'];
        }

        if ($totalAllocated != $receivedQty && $totalAllocated > 0) {
            $ratio = $receivedQty / $totalAllocated;
            foreach ($allocations as &$allocation) {
                $allocation['quantity'] = round($allocation['quantity'] * $ratio, 4);
            }
        }

        foreach ($allocations as $allocation) {
            StockGRNLedger::create([
                'GRNID' => $allocation['grn_id'],
                'GoodsReceiptId' => $allocation['goods_receipt_id'] ?? null,
                'StockItemId' => $stock->Id,
                'ItemNo' => $transferItem->Item,
                'SKUCode' => $stock->SKUCode . '-TRF-' . $receipt->transfer->TransferId . '-' . substr($allocation['grn_id'], -4),
                'ReceivedQTY' => $allocation['quantity'],
                'RemainingQTY' => $allocation['quantity'],
                'UnitPrice' => $allocation['unit_price'],
                'Store' => $storeId,
                'Branch' => $branchId,
                'ReceivedDate' => now(),
                'SourceType' => 'transfer',
                'SourceReference' => $receipt->transfer->TransferId,
                'ParentLedgerId' => $allocation['ledger_id'] ?? null,
                'CreatedBy' => $userId,
                'CreatedOn' => now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);
        }
    }

    private function allocateFIFOFromSource($transferItem, $storeId, $branchId, $quantity)
    {
        $sourceBranch = $transferItem->transfer->FromBranch;

        $sourceStore = Store::where('BranchID', $sourceBranch)
            ->where('Status', true)
            ->first();

        if (! $sourceStore) {
            throw new Exception("No active store found for source branch {$sourceBranch}");
        }

        $availableBatches = StockGRNLedger::where('ItemNo', $transferItem->Item)
            ->where('Store', $sourceStore->Id)
            ->where('Branch', $sourceBranch)
            ->where('RemainingQTY', '>', 0)
            ->orderBy('ReceivedDate', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $allocations = [];
        $remainingQty = $quantity;

        foreach ($availableBatches as $batch) {
            if ($remainingQty <= 0) {
                break;
            }

            $allocatedQty = min($batch->RemainingQTY, $remainingQty);

            $allocations[] = [
                'ledger_id' => $batch->id,
                'grn_id' => $batch->GRNID,
                'goods_receipt_id' => $batch->GoodsReceiptId,
                'unit_price' => (float) $batch->UnitPrice,
                'quantity' => $allocatedQty,
            ];

            $remainingQty -= $allocatedQty;
        }

        if ($remainingQty > 0) {
            throw new Exception("Insufficient stock in source for FIFO allocation. Short by: {$remainingQty}");
        }

        return $allocations;
    }

    private function calculateAverageCost($transferItem, $receivedQty)
    {
        $batchAllocations = json_decode($transferItem->BatchAllocation, true) ?? [];

        if (empty($batchAllocations)) {
            return $transferItem->UnitCost ?? 0;
        }

        $totalCost = 0;
        $totalQty = 0;

        foreach ($batchAllocations as $allocation) {
            $totalCost += $allocation['unit_price'] * $allocation['quantity'];
            $totalQty += $allocation['quantity'];
        }

        return $totalQty > 0 ? $totalCost / $totalQty : 0;
    }

    private function getAllocationSummary($transferItem)
    {
        $batchAllocations = json_decode($transferItem->BatchAllocation, true) ?? [];

        if (empty($batchAllocations)) {
            return 'FIFO Allocation';
        }

        return collect($batchAllocations)->map(function ($allocation) {
            return $allocation['grn_id'] . ' (' . $allocation['quantity'] . ')';
        })->implode(', ');
    }

    public function delete($receipt)
    {
        return DB::transaction(function () use ($receipt) {
            $receiptId = $receipt->Id;
            $userId = Auth::id();

            if (! $userId) {
                throw new Exception('User not authenticated. Cannot delete receipt.');
            }

            foreach ($receipt->items as $item) {
                $stock = StockItem::where('ItemID', $item->item)
                    ->where('Branch', $receipt->transfer->ToBranch)
                    ->where('Store', $item->Store)
                    ->first();

                if ($stock) {
                    $stock->CurrentQty -= $item->ReceivedQty;

                    if ($stock->CurrentQty > 0) {
                        $remainingValue = StockGRNLedger::where('StockItemId', $stock->Id)
                            ->where('RemainingQTY', '>', 0)
                            ->get()
                            ->sum(function ($entry) {
                                return $entry->RemainingQTY * $entry->UnitPrice;
                            });

                        $stock->UnitCost = $remainingValue / $stock->CurrentQty;
                    }

                    $stock->ModifiedBy = $userId;
                    $stock->ModifiedOn = now();
                    $stock->save();
                }

                StockGRNLedger::where('SourceType', 'transfer')
                    ->where('SourceReference', $receipt->transfer->TransferId)
                    ->where('ItemNo', $item->item)
                    ->where('Store', $item->Store)
                    ->delete();

                StockTransaction::where('ReferenceID', $receiptId)
                    ->where('ItemID', $item->item)
                    ->delete();

                $transferSource = CodeDetail::where('CodeID', 'Source')
                    ->where('Description', 'Transfer Receipts')
                    ->value('ID');

                if ($transferSource) {
                    InventoryHold::where('Source', $transferSource)
                        ->where('SourceID', $receiptId)
                        ->delete();
                }
            }

            $transfer = $receipt->transfer;
            if ($transfer && $transfer->Status == Transfers::Delivered->value) {
                $transfer->Status = Transfers::InTransit->value;
                $transfer->save();
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($receipt)
                ->withProperties(['attributes' => $receipt->toArray()])
                ->log("Transaction Receipt deleted (ID: {$receiptId})");

            return $receipt->delete();
        });
    }
}
