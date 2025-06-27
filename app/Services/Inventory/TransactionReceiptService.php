<?php

namespace App\Services\Inventory;

use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\StockItem;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Enums\Inventory\Transfers;

class TransactionReceiptService
{
    public function createReceipt($validatedData, $items)
    {
        return DB::transaction(function () use ($validatedData, $items) {
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

            $transfer = $receipt->transfer;
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

    foreach ($items as $index => $itemData) {
        $storeId = $itemData['store_id'] ?? null;
        $itemId = $itemData['item'];

        if ($storeId) {
            $stock = StockItem::where('ItemID', $itemId)
                ->where('BranchID', $toBranchId)
                ->where('Store', $storeId)
                ->first();

            if (!$stock) {
                throw ValidationException::withMessages([
                    "items.$index.item" => 'Item not available in the selected <strong>Store</strong> Stock. <a href="' . route('sku.create') . '" target="_blank">Click here to add stock</a>.'
                ]);
            }
        } else {
            $stock = StockItem::where('ItemID', $itemId)
                ->where('Branch', $toBranchId)
                ->first();

            if (!$stock) {
                throw ValidationException::withMessages([
                    "items.$index.item" => 'Item not available in the selected <strong>Branch</strong> Stock. <a href="' . route('sku.create') . '" target="_blank">Click here to add stock</a>.'
                ]);
            }
        }

        $receiptItem = $receipt->items()->create([
            'item' => $itemId,
            'Store' => $storeId,
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

        activity()
            ->causedBy(auth()->user())
            ->performedOn($receiptItem)
            ->withProperties(['attributes' => $receiptItem->toArray()])
            ->log('Receipt item added and stock updated');
    }
}

    public function updateReceipt($receipt, $data)
    {
        return DB::transaction(function () use ($receipt, $data) {
            \Log::info('Updating receipt with data:', $data);

            $receipt->update([
                'ReceivedBy' => $data['ReceivedBy'],
                'ReceivedDate' => $data['ReceivedDate'],
                'GeneralRemarks' => $data['GeneralRemarks'] ?? null,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now(),
            ]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($receipt)
                ->withProperties(['attributes' => $receipt->toArray()])
                ->log('Transaction Receipt updated');

            $updatedItemIds = collect($data['items'])->pluck('item')->toArray();
            $receipt->items()->whereNotIn('Item', $updatedItemIds)->delete();

            foreach ($data['items'] as $itemData) {
                $item = $receipt->items()->updateOrCreate(
                    [
                        'ReceiptId' => $receipt->Id,
                        'Item' => $itemData['item']
                    ],
                    [
                        'ReceivedQty' => $itemData['received_qty'],
                        'DamagedQty' => $itemData['damaged_qty'] ?? 0,
                        'DispatchedQty' => $itemData['dispatched_qty'] ?? null,
                        'Discrepancy' => $itemData['discrepancy'] ?? null,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => Carbon::now(),
                    ]
                );

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($item)
                    ->withProperties(['attributes' => $item->toArray()])
                    ->log('Receipt item updated or created');
            }

            return $receipt;
        });
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
