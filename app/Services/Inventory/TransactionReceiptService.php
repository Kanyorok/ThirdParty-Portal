<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\TransactionTransfer;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Validation\ValidationException;
use Log;

class TransactionReceiptService
{
    protected ApprovalWorkflow $workflow;

    public function __construct(ApprovalWorkflow $workflow)
    {
        $this->workflow = new ApprovalWorkflow('TransferStatus', 'Status');
    }

    public function createReceipt($validatedData, $items)
    {
        return DB::transaction(function () use ($validatedData, $items) {
            // Get authenticated user ID BEFORE the transaction
            $userId = Auth::id();
            if (!$userId) {
                throw new Exception('User not authenticated. Cannot create receipt.');
            }

            $transfer = TransactionTransfer::findOrFail($validatedData['TransferID']);

            $deliveredValue = Transfers::Delivered->value;
            $inTransitValue = Transfers::InTransit->value;

            // Create the receipt
            $receipt = TransactionReceipt::create([
                'TransferId' => $validatedData['TransferID'],
                'ReceivedBy' => $validatedData['ReceivedBy'],
                'ReceivedDate' => $validatedData['ReceivedDate'],
                'GeneralRemarks' => $validatedData['GeneralRemarks'] ?? null,
                'Status' => $deliveredValue,
                'CreatedBy' => $userId, // Use the pre-fetched user ID
                'CreatedOn' => now(),
                'ModifiedBy' => $userId, // Use the pre-fetched user ID
                'ModifiedOn' => now(),
            ]);

            $receipt->ReceiptId = 'REC/' . now()->format('Ymd') . '/' . str_pad($receipt->Id, 4, '0', STR_PAD_LEFT);
            $receipt->save();

            Log::info('Transaction receipt created', [
                'receipt_id' => $receipt->Id,
                'transfer_id' => $transfer->Id,
                'user_id' => $userId
            ]);

            // Update transfer status to Delivered
            if ($transfer && $transfer->Status == $inTransitValue) {
                // First update the transfer status
                $transfer->Status = $deliveredValue;
                $transfer->ModifiedBy = $userId;
                $transfer->ModifiedOn = now();
                $transfer->save();
                
                Log::info('Transfer status updated to Delivered', [
                    'transfer_id' => $transfer->Id,
                    'previous_status' => $inTransitValue,
                    'new_status' => $deliveredValue
                ]);

                // Get the User model for workflow approval
                $user = Auth::user();
                if ($user) {
                    // Use the workflow's approve method to record the status change
                    $this->workflow->approve($transfer, $user, Transfers::Delivered, 'Transfer received and marked as Delivered', 'Status');
                    
                    Log::info('Workflow approval recorded for transfer receipt', [
                        'transfer_id' => $transfer->Id,
                        'user_id' => $userId,
                        'status' => $deliveredValue
                    ]);
                } else {
                    Log::warning('User model not found for workflow approval', ['user_id' => $userId]);
                }
            }

            // Create receipt items and update stock
            $this->createReceiptItems($receipt, $items, $userId);

            // Update inventory holds from In Transit to Delivered
            $sourceCodeId = CodeDetail::where('CodeID', 'Source')
                ->where('Description', 'Transaction Transfer')
                ->value('ID');

            if ($sourceCodeId) {
                $updatedCount = InventoryHold::where('Source', $sourceCodeId)
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
                
                Log::info('Inventory holds updated', [
                    'transfer_id' => $receipt->TransferId,
                    'updated_count' => $updatedCount
                ]);
            }

            activity()
                ->causedBy($user ?? Auth::user()) // Fallback to Auth::user()
                ->performedOn($receipt)
                ->withProperties(['attributes' => $receipt->toArray()])
                ->log('Transaction Receipt created and marked as Delivered');

            return $receipt;
        });
    }

    public function createReceiptItems($receipt, $items, $userId = null)
    {
        // Use provided userId or get from Auth
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
            Log::error('Transfer Source CodeDetail ID not found.');
            throw new Exception("Source type 'Transfer Receipts' not found in t_CodeDetails.");
        }

        foreach ($items as $index => $itemData) {
            $storeId = $itemData['store_id'] ?? null;
            $itemId = $itemData['item'];

            $stock = StockItem::where('ItemID', $itemId)
                ->where('Branch', $toBranchId)
                ->when($storeId, fn($q) => $q->where('Store', $storeId))
                ->first();

            if (!$stock) {
                Log::info("Auto-creating new stock record for ItemID: {$itemId}, Branch: {$toBranchId}, Store: {$storeId}");

                $stock = StockItem::firstOrCreate(
                    [
                        'ItemID' => $itemId,
                        'Branch' => $toBranchId,
                        'Store'  => $storeId,
                    ],
                    [
                        'UOM' => $itemData['uom'] ?? null,
                        'UnitCost' => $itemData['unit_cost'] ?? 0,
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
                    ]
                );
            }

            $receiptItem = $receipt->items()->create([
                'item' => $itemId,
                'Store' => $storeId,
                'UnitCost' => $itemData['unit_cost'] ?? null,
                'UOM' => $itemData['uom'],
                'ReceivedQty' => $itemData['received_qty'],
                'DispatchedQty' => $itemData['dispatched_qty'] ?? null,
                'Discrepancy' => isset($itemData['dispatched_qty'], $itemData['received_qty'])
                    ? $itemData['dispatched_qty'] - $itemData['received_qty']
                    : null,
                'DamagedQty' => $itemData['damaged_qty'] ?? 0,
                'CreatedBy' => $userId,
                'CreatedOn' => now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);

            // Update stock quantity
            $previousQty = $stock->CurrentQty;
            $stock->CurrentQty += $itemData['received_qty'];
            $stock->ModifiedBy = $userId;
            $stock->ModifiedOn = now();
            $stock->save();

            Log::info('Stock updated for receipt item', [
                'item_id' => $itemId,
                'store_id' => $storeId,
                'previous_qty' => $previousQty,
                'received_qty' => $itemData['received_qty'],
                'new_qty' => $stock->CurrentQty,
                'branch_id' => $toBranchId
            ]);

            // Generate SKU ID
            $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                ->orderByDesc('id')
                ->value('SKUID');

            $nextNumber = $latestSKU
                ? ((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1
                : 1;

            $skuId = 'SKU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $receiptTypeId = CodeDetail::where('CodeID', 'Source')
                ->where('Description', 'Transfer Receipts')
                ->value('ID');

            if (!$receiptTypeId) {
                throw new Exception("Transfer Receipts source type not found in t_CodeDetails.");
            }

            // Get last balance for this item/store/branch
            $lastToQty = StockTransaction::where('ItemID', $itemId)
                ->where('BranchID', $toBranchId)
                ->where('StoreID', $storeId)
                ->orderByDesc('TransactionDate')
                ->orderByDesc('id')
                ->value('BalanceQty');

            if ($lastToQty === null) {
                $lastToQty = $stock->CurrentQty - $itemData['received_qty'];
            }

            $newToQty = $lastToQty + $itemData['received_qty'];

            // Create stock transaction
            StockTransaction::create([
                'SKUID' => $skuId,
                'TransactionType' => $receiptTypeId,
                'ReferenceID' => $receipt->Id,
                'ItemID' => $itemId,
                'StoreID' => $storeId,
                'BranchID' => $toBranchId,
                'UnitCost' => $itemData['unit_cost'] ?? 0,
                'UOMID' => $itemData['uom'],
                'QuantityIn' => $itemData['received_qty'],
                'QuantityOut' => 0,
                'BalanceQty' => $newToQty,
                'TransactionDate' => now(),
                'TotalCost' => ($itemData['unit_cost'] ?? 0) * ($itemData['received_qty'] ?? 0),
                'Remarks' => 'Transfer From Branch ID ' . ($receipt->transfer->FromBranch ?? 'Unknown'),
                'CreatedBy' => $userId,
                'CreatedOn' => now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);

            Log::info('Stock transaction recorded', [
                'item_id' => $itemId,
                'store_id' => $storeId,
                'quantity_in' => $itemData['received_qty'],
                'transaction_type' => 'Receipt',
                'reference_id' => $receipt->Id,
                'sku_id' => $skuId
            ]);

            // Handle Damaged Items
            $damagedQty = (float)($itemData['damaged_qty'] ?? 0);
            if ($damagedQty > 0) {
                Log::info('Recording damaged items to InventoryHold', [
                    'item_id' => $itemId,
                    'quantity' => $damagedQty,
                    'source_id' => $receipt->Id,
                ]);

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
                ->causedBy(Auth::user()) // Use Auth facade for activity log
                ->performedOn($receiptItem)
                ->withProperties(['attributes' => $receiptItem->toArray()])
                ->log('Receipt item added and stock updated');
        }
    }

    public function delete($receipt)
    {
        return DB::transaction(function () use ($receipt) {
            $receiptId = $receipt->Id;
            $userId = Auth::id();
            
            if (!$userId) {
                throw new Exception('User not authenticated. Cannot delete receipt.');
            }

            Log::warning('Deleting transaction receipt', [
                'receipt_id' => $receiptId,
                'transfer_id' => $receipt->TransferId,
                'user_id' => $userId
            ]);

            // Reverse stock transactions
            foreach ($receipt->items as $item) {
                $stock = StockItem::where('ItemID', $item->item)
                    ->where('Branch', $receipt->transfer->ToBranch)
                    ->where('Store', $item->Store)
                    ->first();

                if ($stock) {
                    $previousQty = $stock->CurrentQty;
                    $stock->CurrentQty -= $item->ReceivedQty;
                    $stock->ModifiedBy = $userId;
                    $stock->ModifiedOn = now();
                    $stock->save();

                    Log::info('Stock reversed for receipt deletion', [
                        'item_id' => $item->item,
                        'store_id' => $item->Store,
                        'previous_qty' => $previousQty,
                        'reversed_qty' => $item->ReceivedQty,
                        'new_qty' => $stock->CurrentQty
                    ]);
                }

                // Delete related stock transactions
                StockTransaction::where('ReferenceID', $receiptId)
                    ->where('ItemID', $item->item)
                    ->delete();

                // Delete inventory holds for damaged items
                $transferSource = CodeDetail::where('CodeID', 'Source')
                    ->where('Description', 'Transfer Receipts')
                    ->value('ID');

                if ($transferSource) {
                    InventoryHold::where('Source', $transferSource)
                        ->where('SourceID', $receiptId)
                        ->delete();
                }
            }

            // Update transfer status back to In Transit
            $transfer = $receipt->transfer;
            if ($transfer && $transfer->Status == Transfers::Delivered->value) {
                $transfer->Status = Transfers::InTransit->value;
                $transfer->save();
                
                Log::info('Transfer status reverted to In Transit', [
                    'transfer_id' => $transfer->Id,
                    'new_status' => Transfers::InTransit->value
                ]);
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