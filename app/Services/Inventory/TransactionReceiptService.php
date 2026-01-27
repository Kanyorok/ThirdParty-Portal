<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockGRNLedger;
use App\Models\Inventory\Store;
use App\Models\Inventory\StockTransaction;
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
            if (!$userId) {
                throw new Exception('User not authenticated. Cannot create receipt.');
            }

            $transfer = TransactionTransfer::with(['items.item'])->findOrFail($validatedData['TransferID']);

            $deliveredValue = Transfers::Delivered->value;
            $inTransitValue = Transfers::InTransit->value;

            // Create the receipt
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

            // Update transfer status
            if ($transfer && $transfer->Status == $inTransitValue) {
                $transfer->Status = $deliveredValue;
                $transfer->ModifiedBy = $userId;
                $transfer->ModifiedOn = now();
                $transfer->save();
            }

            // Create receipt items and update stock with FIFO logic
            $this->createReceiptItemsWithFIFO($receipt, $items, $transfer, $userId);

            // Update inventory holds from In Transit to Delivered
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
        if (!$userId) {
            $userId = Auth::id();
            if (!$userId) {
                throw new Exception('User not authenticated. Cannot create receipt items.');
            }
        }

        $toBranchId = $receipt->transfer->ToBranch;

        $transferSource = CodeDetail::where('CodeID', 'Source')
            ->where('Description', 'Transfer Receipts')
            ->value('ID');

        if (!$transferSource) {
            throw new Exception("Source type 'Transfer Receipts' not found in t_CodeDetails.");
        }

        // Group items by store for processing
        $itemsByStore = collect($items)->groupBy('store_id');

        foreach ($itemsByStore as $storeId => $storeItems) {
            foreach ($storeItems as $itemData) {
                $itemId = $itemData['item'];
                $receivedQty = $itemData['received_qty'];
                $damagedQty = $itemData['damaged_qty'] ?? 0;
                
                // Get the corresponding transfer item to get batch allocations
                $transferItem = $transfer->items()->where('Item', $itemId)->first();
                
                if (!$transferItem) {
                    throw new Exception("Transfer item not found for item {$itemId}");
                }

                // Find or create stock item at destination
                $stock = StockItem::where('ItemID', $itemId)
                    ->where('Branch', $toBranchId)
                    ->where('Store', $storeId)
                    ->first();

                $averageCost = $this->calculateAverageCost($transferItem, $receivedQty);

                if (!$stock) {
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
                    // Recalculate weighted average cost for existing stock
                    $oldQty = $stock->CurrentQty;
                    $oldCost = $stock->UnitCost;
                    $newQty = $receivedQty;
                    
                    if ($oldQty > 0) {
                        $stock->UnitCost = (($oldQty * $oldCost) + ($newQty * $averageCost)) / ($oldQty + $newQty);
                    } else {
                        $stock->UnitCost = $averageCost;
                    }
                }

                // Create receipt item
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

                // Update stock quantity (only good received items)
                $stock->CurrentQty += $receivedQty;
                $stock->ModifiedBy = $userId;
                $stock->ModifiedOn = now();
                $stock->save();

                // CREATE STOCKGRNLEDGER ENTRIES WITH GRN ALLOCATIONS
                $this->createGRNLedgerEntriesForTransfer($receipt, $transferItem, $stock, $storeId, $toBranchId, $receivedQty, $userId);

                // Generate SKU ID for stock transaction
                $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                    ->orderByDesc('id')
                    ->value('SKUID');

                $nextNumber = $latestSKU
                    ? ((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1
                    : 1;

                $skuId = 'SKU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                // Get last balance for this item/store/branch
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

                // Create stock transaction
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

                // Handle Damaged Items
                if ($damagedQty > 0) {
                    $damagedReasonId = CodeDetail::where('CodeID', 'AdjustmentReason')
                        ->where('Description', 'Damaged in Transit')
                        ->value('ID');

                    if (!$damagedReasonId) {
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

    /**
     * Create StockGRNLedger entries for transfer receipt
     */
    private function createGRNLedgerEntriesForTransfer($receipt, $transferItem, $stock, $storeId, $branchId, $receivedQty, $userId)
    {
        // Get batch allocations from transfer item
        $batchAllocations = json_decode($transferItem->BatchAllocation, true) ?? [];
        
        if (empty($batchAllocations)) {
            // If no specific allocations, use FIFO from source
            $allocations = $this->allocateFIFOFromSource($transferItem, $storeId, $branchId, $receivedQty);
        } else {
            // Use the pre-allocated batches from transfer
            $allocations = $batchAllocations;
        }

        $totalAllocated = 0;
        foreach ($allocations as $allocation) {
            $totalAllocated += $allocation['quantity'];
        }

        // If total allocated doesn't match received (due to damages), adjust proportionally
        if ($totalAllocated != $receivedQty && $totalAllocated > 0) {
            $ratio = $receivedQty / $totalAllocated;
            foreach ($allocations as &$allocation) {
                $allocation['quantity'] = round($allocation['quantity'] * $ratio, 4);
            }
        }

        // Create StockGRNLedger entries for EACH allocation (multiple GRN IDs)
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
                'ParentLedgerId' => $allocation['ledger_id'] ?? null, // Link to source ledger
                'CreatedBy' => $userId,
                'CreatedOn' => now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);
        }
    }

    /**
     * Allocate FIFO from source when no specific allocations exist
     */
    private function allocateFIFOFromSource($transferItem, $storeId, $branchId, $quantity)
    {
        // Get source branch (from transfer)
        $sourceBranch = $transferItem->transfer->FromBranch;
        
        // Get default store for source branch
        $sourceStore = Store::where('BranchID', $sourceBranch)
            ->where('Status', true)
            ->first();
            
        if (!$sourceStore) {
            throw new Exception("No active store found for source branch {$sourceBranch}");
        }

        // Get available batches from source in FIFO order
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
            if ($remainingQty <= 0) break;
            
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

    /**
     * Calculate average cost from allocations
     */
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

    /**
     * Get allocation summary for remarks
     */
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

            if (!$userId) {
                throw new Exception('User not authenticated. Cannot delete receipt.');
            }

            foreach ($receipt->items as $item) {
                $stock = StockItem::where('ItemID', $item->item)
                    ->where('Branch', $receipt->transfer->ToBranch)
                    ->where('Store', $item->Store)
                    ->first();

                if ($stock) {
                    $stock->CurrentQty -= $item->ReceivedQty;
                    
                    // Recalculate unit cost after deletion
                    if ($stock->CurrentQty > 0) {
                        // Get remaining ledger entries
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

                // Delete StockGRNLedger entries for this receipt
                StockGRNLedger::where('SourceType', 'transfer')
                    ->where('SourceReference', $receipt->transfer->TransferId)
                    ->where('ItemNo', $item->item)
                    ->where('Store', $item->Store)
                    ->delete();

                // Delete stock transactions
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