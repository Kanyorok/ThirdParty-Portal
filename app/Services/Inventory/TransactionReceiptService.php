<?php

namespace App\Services\Inventory;

use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\TransactionReceiptItem;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
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

            // Add receipt items
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
        foreach ($items as $itemData) {
            $item = $receipt->items()->create([
                'item' => $itemData['item'],
                'ReceivedQty' => $itemData['received_qty'],
                'DispatchedQty' => $itemData['dispatched_qty'] ?? null,
                'Discrepancy' => isset($itemData['dispatched_qty'], $itemData['received_qty']) 
                    ? $itemData['dispatched_qty'] - $itemData['received_qty'] 
                    : null,
                'DamagedQty' => $itemData['damaged_qty'] ?? 0,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => Carbon::now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($item)
                ->withProperties(['attributes' => $item->toArray()])
                ->log('Receipt item added');
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
