<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Branch;
use App\Models\Core\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Procurement\GoodsReceipt;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory\StockTransaction;

use Throwable;

class TransactionTransferService
{
public function getHQBranchId(): int
    {
        $hqBranch = Branch::where('IsHQ', 1)->first();
        if (!$hqBranch) {
            throw new Exception('No HQ branch defined. Please set a branch as HQ.');
        }
        return $hqBranch->Id;
    }

    public function createTransfer(array $data): TransactionTransfer
    {
        $data['Status'] = Transfers::Pending;

        if ($data['RequisitionType'] === 'procurement') {
            $requisition = GoodsReceipt::findOrFail($data['RequisitionId']);
            $fromBranch = $this->getHQBranchId();
            $toBranch = $requisition->TransferTo;
        } else {
            $requisition = InterBranchRequisition::findOrFail($data['RequisitionId']);
            $fromBranch = $requisition->FromBranch;
            $toBranch = $requisition->ToBranch;
        }

        if (empty($data['TransferDate'])) {
            throw new Exception('TransferDate is required.');
        }

        $transfer = new TransactionTransfer([
            'TransferDate' => $data['TransferDate'],
            'TransferredBy' => $data['TransferredBy'],
            'RequisitionId' => $data['RequisitionId'],
            'FromBranch' => $fromBranch,
            'ToBranch' => $toBranch,
            'RequisitionType' => $data['RequisitionType'],
            'Status' => $data['Status'],
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ]);
        $transfer->save();

        $transfer->TransferId = $this->generateTransferId($transfer);
        $transfer->save();

        Workflow::create([
            'Source' => 'TransactionTransfer',
            'SourceID' => $transfer->Id,
            'Stage' => Transfers::Pending->label(),
            'Status' => Transfers::Pending->value,
            'Notes' => 'Transaction Transfers Pending',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::updateOrCreate(
            ['Source' => 'TransactionTransfer', 'SourceID' => $transfer->Id],
            [
                'Stage' => Transfers::Pending->label(),
                'UserId' => Auth::id(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]
        );

        activity()->performedOn($transfer)->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Created Transaction Transfer');

        return $transfer;
    }

    public function createTransferItems(TransactionTransfer $transfer, array $items): void
    {
        foreach ($items as $itemData) {

            $itemId = $itemData['item'];
            $dispatchedQty = $itemData['dispatched_qty'];

            $fromBranch = $transfer->RequisitionType === 'procurement'
                ? $this->getHQBranchId()
                : $transfer->FromBranch;

            // $stock = StockItem::where('ItemID', $itemId)
            //     ->where('Branch', $fromBranch)
            //     ->first();

            // if (!$stock || $stock->CurrentQty < $dispatchedQty) {
            //     throw new Exception("Insufficient stock for ItemID {$itemId} in Branch {$fromBranch}.");
            // }



            $created = TransactionTransferItem::create([
                'TransferId' => $transfer->Id,
                'Item' => $itemId,
                'ApprovedQty' => $itemData['approved_qty'],
                'UnitCost' => $itemData['unit_cost'] ?? null,
                'UOM' => $itemData['uom'],
                'DispatchedQty' => $dispatchedQty,
                'Remarks' => $itemData['remarks'] ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);

            activity()->performedOn($created)->causedBy(Auth::user())
                ->withProperties(['attributes' => $itemData])
                ->log('Created Transaction Transfer Item');
        }
    }

    public function update(TransactionTransfer $transfer, array $data): void
        {
            DB::transaction(function () use ($transfer, $data) {

                // Update main transfer fields
                $transfer->TransferDate = $data['TransferDate'] ?? $transfer->TransferDate;
                $transfer->TransferredBy = $data['TransferredBy'] ?? $transfer->TransferredBy;
                $transfer->ModifiedBy = auth()->id();
                $transfer->ModifiedOn = now();
                $transfer->save();

                // Update transfer items
                if (!empty($data['items'])) {

                    foreach ($data['items'] as $itemData) {

                        $transferItem = $transfer->items()->where('Item', $itemData['item'])->first();

                        if ($transferItem) {
                            $transferItem->ApprovedQty = $itemData['approved_qty'];
                            $transferItem->DispatchedQty = $itemData['dispatched_qty'];
                            $transferItem->Remarks = $itemData['remarks'] ?? null;
                            $transferItem->UnitCost = $itemData['unit_cost'] ?? $transferItem->UnitCost;
                            $transferItem->ModifiedBy = auth()->id();
                            $transferItem->ModifiedOn = now();
                            $transferItem->save();

                            activity()->performedOn($transferItem)
                                ->causedBy(auth()->user())
                                ->withProperties(['attributes' => $itemData])
                                ->log('Updated Transaction Transfer Item');
                        }
                    }
                }

                activity()->performedOn($transfer)
                    ->causedBy(auth()->user())
                    ->withProperties(['attributes' => $data])
                    ->log('Updated Transaction Transfer');
            });
        }

        public function approve(int $id): void
        {
            DB::beginTransaction();

            try {
                $transfer = TransactionTransfer::with('items')->findOrFail($id);
                $transfer->Status = Transfers::InTransit;
                $transfer->ModifiedBy = Auth::id();
                $transfer->ModifiedOn = now();
                $transfer->save();

                foreach ($transfer->items as $item) {
                    $stockFrom = StockItem::where('ItemID', $item->Item)
                        ->where('Branch', $transfer->FromBranch)
                        ->first();

                    if ($stockFrom) {
                        $stockFrom->CurrentQty -= $item->DispatchedQty;
                        $stockFrom->ModifiedBy = Auth::id();
                        $stockFrom->ModifiedOn = now();
                        $stockFrom->save();
                    }

                    InventoryHold::create([
                        'ItemID' => $item->Item,
                        'BranchID' => $transfer->ToBranch,
                        'Quantity' => $item->DispatchedQty,
                        'Reason' => CodeDetail::where('CodeID', 'AdjustmentReason')->where('Description', 'In Transit')->value('ID'),
                        'Source' => CodeDetail::where('CodeID', 'Source')->where('Description', 'Transaction Transfer')->value('ID'),
                        'SourceID' => $transfer->Id,
                        'Status' => Transfers::InTransit->value,
                        'Remarks' => $item->Remarks,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);

                $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                    ->orderByDesc('id') 
                    ->value('SKUID');

                if ($latestSKU) {
                    $number = (int) preg_replace('/[^0-9]/', '', $latestSKU);
                    $nextNumber = str_pad($number + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $nextNumber = '001';
                }

                $skuId = 'SKU' . $nextNumber;

               $lastToQty = StockTransaction::where('ItemID', $item->Item)
                    ->where('BranchID', $transfer->FromBranch)
                    ->orderByDesc('TransactionDate')
                    ->orderByDesc('id')
                    ->value('BalanceQty');

                if ($lastToQty === null) {
                    $lastToQty = StockItem::where('ItemID', $item->Item)
                        ->where('Branch', $transfer->FromBranch)
                        ->value('CurrentQty') ?? 0;
                }

                $newToQty = $lastToQty - $item->DispatchedQty;

                $dispatchedQty = $item->DispatchedQty;
                $totalCost = ($item->UnitCost ?? 0) * $dispatchedQty;

                // Make totalCost negative if it's a stock-out
                if ($dispatchedQty > 0) {
                    $totalCost *= -1;
                }
            StockTransaction::create([
                'SKUID' => $skuId,
                    'TransactionType' => CodeDetail::where('CodeID', 'Source')->where('Description', 'Transaction Transfer')->value('ID'),
                    'ItemID' => $item->Item,
                    'StoreID' => $stockFrom->Store ?? null, 
                    'BranchID' => $transfer->FromBranch,
                    'UnitCost' => $item->UnitCost,
                    'UOMID' => $item->uom->Id,
                    'QuantityIn' => 0,
                    'QuantityOut' => $item->DispatchedQty,
                    'BalanceQty' => $newToQty,
                    'TotalCost' => $totalCost,
                    'TransactionDate' => now(),
                    'ReferenceID' => $transfer->Id,
                    'Remarks' => 'Transfer to Branch ID ' . $transfer->ToBranch,
                    'CreatedBy' => Auth::id(),
                    'CreatedDate' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);
                        }

                Workflow::create([
                    'Source' => 'TransactionTransfer',
                    'SourceID' => $transfer->Id,
                    'Stage' => Transfers::InTransit->label(),
                    'Status' => Transfers::InTransit->value,
                    'Notes' => 'Transaction Transfer Approved: stock deducted from origin branch',
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                PendingWorkflow::where('Source', 'TransactionTransfer')
                    ->where('SourceID', $transfer->Id)
                    ->update(['Stage' => Transfers::InTransit->label()]);

                activity()->performedOn($transfer)->causedBy(Auth::user())
                    ->withProperties(['attributes' => $transfer->toArray()])
                    ->log('Approved Transaction Transfer: stock deducted and transaction recorded');

                DB::commit();
            } catch (Throwable $th) {
                DB::rollBack();
                Log::error('Transfer approval failed: ' . $th->getMessage(), [
                    'transfer_id' => $id,
                    'user_id' => Auth::id(),
                    'exception' => $th,
                ]);
                throw $th;
            }
        }

    public function reject(int $id): void
    {
        $transfer = TransactionTransfer::findOrFail($id);
        $transfer->Status = Transfers::Rejected;
        $transfer->ModifiedBy = Auth::id();
        $transfer->ModifiedOn = now();
        $transfer->save();

        Workflow::create([
            'Source' => 'TransactionTransfer',
            'SourceID' => $transfer->Id,
            'Stage' => Transfers::Rejected->label(),
            'Status' => Transfers::Rejected->value,
            'Notes' => 'Transaction Transfer Rejected',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::where('Source', 'TransactionTransfer')
            ->where('SourceID', $transfer->Id)
            ->update(['Stage' => Transfers::Rejected->label()]);

        activity()->performedOn($transfer)->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Rejected Transaction Transfer');
    }

    protected function generateTransferId(TransactionTransfer $transfer): string
    {
        $year = now()->format('Y');
        return 'TRF-' . $year . '-' . str_pad($transfer->Id, 4, '0', STR_PAD_LEFT);
    }


    public function getApprovedTransfers()
    {
        return TransactionTransfer::where('Status', Transfers::InTransit)
            ->orderByDesc('CreatedOn')
            ->get([
                'Id', 
                'TransferId', 
                'TransferDate',
                'FromBranch',
                'ToBranch'
            ]);
    }


}
