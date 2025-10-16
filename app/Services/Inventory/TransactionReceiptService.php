<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\TransactionReceipt;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Log;

class TransactionReceiptService
{
   public function createReceipt($validatedData, $items)
{
    return DB::transaction(function () use ($validatedData, $items) {
        $transfer = \App\Models\Inventory\TransactionTransfer::findOrFail($validatedData['TransferID']);

        $receipt = TransactionReceipt::create([
            'TransferId' => $validatedData['TransferID'],
            'ReceivedBy' => $validatedData['ReceivedBy'],
            'ReceivedDate' => $validatedData['ReceivedDate'],
            'GeneralRemarks' => $validatedData['GeneralRemarks'] ?? null,
            'Status' => Transfers::Delivered,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        if ($transfer && ($transfer->Status == Transfers::InTransit || $transfer->Status === Transfers::InTransit->value)) {
            $transfer->Status = Transfers::Delivered;
            $transfer->save();
        }

        $receipt->ReceiptId = 'REC/' . now()->format('Ymd') . '/' . str_pad($receipt->Id, 4, '0', STR_PAD_LEFT);
        $receipt->save();

        $this->createReceiptItems($receipt, $items);

        Workflow::create([
            'Source' => 'TransactionReceipts',
            'SourceID' => $receipt->Id,
            'Stage' => Transfers::Delivered->label(),
            'Status' => Transfers::Delivered->value,
            'Notes' => 'Transaction Receipts Delivered',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::updateOrCreate(
            ['Source' => 'TransactionReceipts', 'SourceID' => $receipt->Id],
            [
                'Stage' => Transfers::Delivered->label(),
                'UserId' => Auth::id(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]
        );

        $sourceCodeId = CodeDetail::where('CodeID', 'Source')
            ->where('Description', 'Transaction Transfer')
            ->value('ID');

        $deliveredStatus = Transfers::Delivered->value;

        InventoryHold::where('Source', $sourceCodeId)
            ->where('SourceID', $receipt->TransferId)
            ->where('Status', Transfers::InTransit->value)
            ->whereNull('DeletedOn')
            ->update([
                'Status' => $deliveredStatus,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
                'DeletedBy' => Auth::id(),
                'DeletedOn' => now(),
            ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($receipt)
            ->withProperties(['attributes' => $receipt->toArray()])
            ->log('Transaction Receipt created');

        return $receipt;
    });
}


    public function createReceiptItems($receipt, $items)
    {
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

            $stockQuery = StockItem::where('ItemID', $itemId)->where('Branch', $toBranchId);
            $storeId ? $stockQuery->where('Store', $storeId) : null;
            $stock = $stockQuery->first();

            if (!$stock) {
                throw ValidationException::withMessages([
                    "items.$index.item" => 'Item not available in the selected stock. <a href="' . route('sku.create') . '" target="_blank">Click here to add stock</a>.'
                ]);
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
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            $stock->CurrentQty += $itemData['received_qty'];
            $stock->ModifiedBy = Auth::id();
            $stock->ModifiedOn = now();
            $stock->save();

            
         //Generate SKU ID
        $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
            ->orderByDesc('id')
            ->value('SKUID');

        if ($latestSKU) {
            $number = (int) preg_replace('/[^0-9]/', '', $latestSKU);
            $nextNumber = $number + 1;
        } else {
            $nextNumber = 1;
        }

        $skuId = 'SKU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $receiptTypeId = CodeDetail::where('CodeID', 'Source')
        ->where('Description', 'Transaction Receipts')
        ->value('ID');

              $lastToQty = StockTransaction::where('SKUID', $skuId)
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
            'TransactionType' => CodeDetail::where('CodeID', 'Source')->where('Description', 'Transfer Receipts')->value('ID'),
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
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);
            Log::info('Stock transaction recorded', [
                'ItemID' => $itemId,
                'StoreID' => $storeId,
                'QuantityIn' => $itemData['received_qty'],
                'TransactionType' => 'Receipt',
                'ReferenceID' => $receipt->Id,
            ]);


            $damagedQty = (float)($itemData['damaged_qty'] ?? 0);
            if ($damagedQty > 0) {
                Log::info('Recording to InventoryHold', [
                    'ItemID' => $itemId,
                    'Quantity' => $damagedQty,
                    'SourceID' => $receipt->Id,
                    'Source' => $transferSource,
                ]);

                $status = CodeDetail::where('CodeID', 'AdjustmentReason')
                    ->where('Description', 'Damaged in Transit')
                    ->value('ID');

             
                InventoryHold::create([
                    'ItemID' => $itemId,
                    'BranchID' => $toBranchId,
                    'Store' => $storeId,
                    'Quantity' => $damagedQty,
                    'Reason' => $status,
                    'Source' => $transferSource,
                    'SourceID' => $receipt->Id,
                    'Status' => Transfers::InTransit->value,
                    'Remarks' => $itemData['remarks'] ?? null,
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);
            }


            activity()
                ->causedBy(auth()->user())
                ->performedOn($receiptItem)
                ->withProperties(['attributes' => $receiptItem->toArray()])
                ->log('Receipt item added and stock updated');
        }
    }


    public function delete($receipt)
    {
        $receiptId = $receipt->Id;

        activity()
            ->causedBy(Auth::user())
            ->performedOn($receipt)
            ->withProperties(['attributes' => $receipt->toArray()])
            ->log("Transaction Receipt deleted (ID: {$receiptId})");

        return $receipt->delete();
    }
}
