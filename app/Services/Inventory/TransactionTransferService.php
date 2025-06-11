<?php

namespace App\Services\Inventory;

use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Inventory\InterBranchRequisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;


class TransactionTransferService
{
    public function createTransfer(array $data): TransactionTransfer
    {
        $requisition = InterBranchRequisition::findOrFail($data['RequisitionId']);

        if (!isset($data['TransferDate']) || empty($data['TransferDate'])) {
        throw new \Exception('TransferDate is required.');}

        // Create the transfer first
        $transfer = new TransactionTransfer($data);
        $transfer->TransferDate = $data['TransferDate'] ?? Carbon::now();
        $transfer->FromBranch = $requisition->FromBranch;
        $transfer->ToBranch = $requisition->ToBranch;
        $transfer->CreatedBy = Auth::id();
        $transfer->ModifiedBy = Auth::id();
        $transfer->CreatedOn = Carbon::now();
        $transfer->ModifiedOn = Carbon::now();
        $transfer->save();

        $transfer->TransferId = $this->generateTransferId($transfer);
        $transfer->save();

        return $transfer; // Return the transfer ID for item processing
    }

    public function createTransferItems(TransactionTransfer $transfer, array $items): void
    {
        //$requisition = TransactionTransfer::findOrFail($data['TransferId']);
        foreach ($items as $item) {
            $item['TransferId'] = $transfer->Id;
            $item['CreatedBy'] = Auth::id();
            $item['ModifiedBy'] = Auth::id();
            $item['CreatedOn'] = Carbon::now();
            $item['ModifiedOn'] = Carbon::now();
            //$items->save();

            TransactionTransferItem::create($item);
            $item->save(); //  Insert into t_TransferItems
        }
    }


    public function update(TransactionTransfer $transfer, array $data): TransactionTransfer
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $transfer->fill($data);
        $transfer->TransferDate = $data['TransferDate'] ?? Carbon::now(); 
        $transfer->ModifiedBy = Auth::id();
        $transfer->ModifiedOn = Carbon::now();
        $transfer->save();

        $transfer->items()->delete();

        foreach ($items as $item) {
            $item['TransferId'] = $transfer->Id;
            $item['CreatedBy'] = Auth::id();
            $item['ModifiedBy'] = Auth::id();
            $item['CreatedOn'] = Carbon::now();
            $item['ModifiedOn'] = Carbon::now();
            TransactionTransferItem::create($item);
        }

        activity()
            ->performedOn($transfer)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data, 'items' => $items])
            ->log('Updated Transaction Transfer');

        return $transfer;
    }

    public function delete(TransactionTransfer $transfer): bool
    {
        $transfer->DeletedBy = Auth::id();
        $transfer->save();
        $transfer->delete();

        $transfer->items()->delete();

        activity()
            ->performedOn($transfer)
            ->causedBy(Auth::user())
            ->log('Deleted Transaction Transfer');

        return true;
    }

    protected function generateTransferId(TransactionTransfer $transfer): string
    {
        $year = now()->format('Y');
        return 'TRF-' . $year . '-' . str_pad($transfer->Id, 4, '0', STR_PAD_LEFT);
    }
}