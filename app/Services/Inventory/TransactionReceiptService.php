<?php

namespace App\Services\Inventory;

use App\Models\Inventory\TransactionReceipt;
use Illuminate\Support\Facades\DB;

class TransactionReceiptService
{
    /**
     * Create the receipt record (excluding items).
     */
    public function createReceipt($validatedData)
    {
        return DB::transaction(function () use ($validatedData) {
            return TransactionReceipt::create([
                'TransferId' => $validatedData['TransferId'],
                'ReceivedBy' => $validatedData['ReceivedBy'],
                'ReceivedDate' => $validatedData['ReceivedDate'],
                'GeneralRemarks' => $validatedData['GeneralRemarks'] ?? null,
            ]);
        });
    }

    /**
     * Create associated receipt items.
     */
    public function createReceiptItems($receipt, $items)
    {
        foreach ($items as $itemData) {
            $receipt->items()->create([
                'item' => $itemData['item'],
                'received_qty' => $itemData['received_qty'],
                'damaged_qty' => $itemData['damaged_qty'] ?? 0,
                'remarks' => $itemData['remarks'] ?? null,
            ]);
        }
    }

    /**
     * Update the receipt and its items.
     */
    public function updateReceipt($receipt, $data)
    {
        return DB::transaction(function () use ($receipt, $data) {
            $receipt->update([
                'ReceivedBy' => $data['ReceivedBy'],
                'ReceivedDate' => $data['ReceivedDate'],
                'GeneralRemarks' => $data['GeneralRemarks'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $receipt->items()->updateOrCreate(
                    ['item' => $itemData['item']],
                    [
                        'received_qty' => $itemData['received_qty'],
                        'damaged_qty' => $itemData['damaged_qty'] ?? 0,
                        'remarks' => $itemData['remarks'] ?? null,
                    ]
                );
            }

            return $receipt;
        });
    }

    /**
     * Delete the receipt.
     */
    public function deleteReceipt($receipt)
    {
        return $receipt->delete();
    }
}
