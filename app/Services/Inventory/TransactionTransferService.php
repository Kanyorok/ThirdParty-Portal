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

       if (empty($data['TransferDate'])) {
        throw new \Exception('TransferDate is required.');
    }

        $transfer = new TransactionTransfer();
        $transfer->TransferDate = $data['TransferDate'];
        $transfer->TransferredBy = $data['TransferredBy'];
        $transfer->RequisitionId = $data['RequisitionId'];
        $transfer->FromBranch = $requisition->FromBranch;
        $transfer->ToBranch = $requisition->ToBranch;
        $transfer->CreatedBy = Auth::id();
        $transfer->ModifiedBy = Auth::id();
        $transfer->CreatedOn = Carbon::now();
        $transfer->ModifiedOn = Carbon::now();
        $transfer->save();

        $transfer->TransferId = $this->generateTransferId($transfer);
        $transfer->save();

        return $transfer; 
    }


public function createTransferItems(TransactionTransfer $transfer, array $items): void
{
    foreach ($items as $item) {
        
        TransactionTransferItem::create([
            'TransferId'   => $transfer->Id,
            'Item'         => $item['item'],          
            'ApprovedQty'  => $item['approved_qty'],
            'DispatchedQty' => $item['dispatched_qty'],
            'Remarks'      => $item['remarks'] ?? null,
            'CreatedBy'    => Auth::id(),
            'ModifiedBy'   => Auth::id(),
            'CreatedOn'    => Carbon::now(),
            'ModifiedOn'   => Carbon::now(),
        ]);
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
    $transfer->items()->delete();
    $transfer->delete();

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