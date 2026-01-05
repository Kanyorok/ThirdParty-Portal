<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\TransactionTransfer;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Workflow\ApprovalWorkflow;

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

            // Create receipt items and update stock
            $this->createReceiptItems($receipt, $items, $userId);

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

    public function createReceiptItems($receipt, $items, $userId = null)
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

        foreach ($items as $itemData) {
            $storeId = $itemData['store_id'] ?? null;
            $itemId = $itemData['item'];

            $stock = StockItem::where('ItemID', $itemId)
                ->where('Branch', $toBranchId)
                ->when($storeId, fn($q) => $q->where('Store', $storeId))
                ->first();

            if (!$stock) {
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
            $stock->CurrentQty += $itemData['received_qty'];
            $stock->ModifiedBy = $userId;
            $stock->ModifiedOn = now();
            $stock->save();

            // Generate SKU ID
            $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                ->orderByDesc('id')
                ->value('SKUID');

            $nextNumber = $latestSKU
                ? ((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1
                : 1;

            $skuId = 'SKU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $receiptTypeId = $transferSource;

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

            // Handle Damaged Items
            $damagedQty = (float)($itemData['damaged_qty'] ?? 0);
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

            foreach ($receipt->items as $item) {
                $stock = StockItem::where('ItemID', $item->item)
                    ->where('Branch', $receipt->transfer->ToBranch)
                    ->where('Store', $item->Store)
                    ->first();

                if ($stock) {
                    $stock->CurrentQty -= $item->ReceivedQty;
                    $stock->ModifiedBy = $userId;
                    $stock->ModifiedOn = now();
                    $stock->save();
                }

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
