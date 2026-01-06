<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Branch;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Procurement\GoodsReceipt;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\StockTransaction;
use App\Services\Workflow\ApprovalWorkflow;
use App\Models\Inventory\InterBranchRequisition;
use App\Services\Finance\TransactionService;
use Throwable;

class TransactionTransferService
{
    protected ApprovalWorkflow $workflow;
    protected TransactionService $transactionService; // Add this property

    public function __construct(ApprovalWorkflow $workflow, TransactionService $transactionService)
    {
        $this->workflow = new ApprovalWorkflow('TransferStatus','Status');
        $this->transactionService = $transactionService; // Initialize it
    }

    public function getHQBranchId(): int
    {
        $hqBranch = Branch::where('IsHQ', 1)->first();
        if (!$hqBranch) {
            throw new Exception('No HQ branch defined. Please set a branch as HQ.');
        }
        return $hqBranch->Id;
    }
    
    protected function generateTransferId(TransactionTransfer $transfer): string
    {
        $year = now()->format('Y');
        return 'TRF-' . $year . '-' . str_pad($transfer->Id, 4, '0', STR_PAD_LEFT);
    }

    public function createTransfer(array $data): TransactionTransfer
    {
        $data['Status'] = Transfers::Pending->value;

        if ($data['RequisitionType'] === 'procurement') {
            $requisition = GoodsReceipt::findOrFail($data['RequisitionId']);
            $fromBranch = $this->getHQBranchId();
            $toBranch = $data['ToBranch']; 
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
            'Status' => Transfers::Pending->value,
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ]);
        $transfer->save();

        $transfer->TransferId = $this->generateTransferId($transfer);
        $transfer->save();

        try {
            // Create workflow instance and submit for approval
            $this->workflow->submit(
                $transfer,
                Auth::user(),
                Transfers::Pending,
                'Transaction Transfer Submitted for Approval'
            );
        } catch (Exception $e) {
            throw $e;
        }

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
            $transfer->TransferDate = $data['TransferDate'] ?? $transfer->TransferDate;
            $transfer->TransferredBy = $data['TransferredBy'] ?? $transfer->TransferredBy;
            
            if ($transfer->RequisitionType === 'procurement' && isset($data['ToBranch'])) {
                $transfer->ToBranch = $data['ToBranch'];
            }
            
            $transfer->ModifiedBy = auth()->id();
            $transfer->ModifiedOn = now();
            $transfer->save();

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

    public function approve(int $id, string $comments = null): void
{
    DB::beginTransaction();

    try {
        $transfer = TransactionTransfer::with('items')->findOrFail($id);
        $user = Auth::user();
        $svc = $this->transactionService; // use injected service

        // Approve workflow
        $this->workflow->approve(
            $transfer,
            $user,
            Transfers::InTransit,
            $comments ?? 'Transfer Approved',
            'Status'
        );

        // Update transfer status to In Transit
        $transfer->Status = Transfers::InTransit->value;
        $transfer->ModifiedBy = $user->Id;
        $transfer->ModifiedOn = now();
        $transfer->save();

        $totalCost = 0;

        foreach ($transfer->items as $item) {
            // Check stock availability
            $stockFrom = StockItem::where('ItemID', $item->Item)
                ->where('Branch', $transfer->FromBranch)
                ->first();

            if (!$stockFrom) {
                throw new Exception("No stock found for Item {$item->Item} in branch {$transfer->FromBranch}");
            }

            if ($stockFrom->CurrentQty < $item->DispatchedQty) {
                throw new Exception("Insufficient stock for Item {$item->Item}. Available: {$stockFrom->CurrentQty}, Required: {$item->DispatchedQty}");
            }

            // Get last balance
            $lastBalance = StockTransaction::where('ItemID', $item->Item)
                ->where('BranchID', $transfer->FromBranch)
                ->orderByDesc('TransactionDate')
                ->orderByDesc('id')
                ->value('BalanceQty') ?? $stockFrom->CurrentQty ?? 0;

            $dispatchedQty = $item->DispatchedQty;
            $newBalance = $lastBalance - $dispatchedQty;

            $unitCost = $item->UnitCost ?? 0;
            $itemTotalCost = $unitCost * $dispatchedQty;
            $totalCost += $itemTotalCost;

            // Generate SKU ID
            $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                ->orderByDesc('id')
                ->value('SKUID');

            $nextNumber = $latestSKU
                ? str_pad(((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1, 3, '0', STR_PAD_LEFT)
                : '001';

            $skuId = 'SKU' . $nextNumber;

            // Transaction type
            $transactionTypeId = CodeDetail::where('CodeID', 'Source')
                ->where('Description', 'Transaction Transfer')->value('ID');

            StockTransaction::create([
                'SKUID' => $skuId,
                'TransactionType' => $transactionTypeId,
                'ItemID' => $item->Item,
                'StoreID' => $stockFrom->Store ?? null,
                'BranchID' => $transfer->FromBranch,
                'UnitCost' => $unitCost,
                'UOMID' => $item->uom->Id ?? null,
                'QuantityIn' => 0,
                'QuantityOut' => $dispatchedQty,
                'BalanceQty' => $newBalance,
                'TotalCost' => $itemTotalCost * -1,
                'TransactionDate' => now(),
                'ReferenceID' => $transfer->Id,
                'Remarks' => 'Transfer to Branch ID ' . $transfer->ToBranch,
                'CreatedBy' => $user->Id,
                'CreatedOn' => now(),
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            // Update stock item
            $stockFrom->CurrentQty = $newBalance;
            $stockFrom->ModifiedBy = $user->Id;
            $stockFrom->ModifiedOn = now();
            $stockFrom->save();

            // Create inventory hold
            $reasonId = CodeDetail::where('CodeID', 'AdjustmentReason')
                ->where('Description', 'In Transit')->value('ID');
            $sourceId = CodeDetail::where('CodeID', 'Source')
                ->where('Description', 'Transaction Transfer')->value('ID');

            InventoryHold::create([
                'ItemID' => $item->Item,
                'BranchID' => $transfer->ToBranch,
                'Quantity' => $dispatchedQty,
                'Reason' => $reasonId,
                'Source' => $sourceId,
                'SourceID' => $transfer->Id,
                'Status' => Transfers::InTransit->value,
                'Remarks' => $item->Remarks,
                'CreatedBy' => $user->Id,
                'CreatedOn' => now(),
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);
        }

        // Post financial transaction if total cost > 0
        if ($totalCost > 0) {
            $payload = [
                'ModuleID'          => 400000,
                'TransactionTypeID' =>  5,
                'TransactionType'   => 'InterBranch Inventory Transfer',
                'ReferenceNumber'   => $transfer->TransferID,
                'TransactionDate'   => now()->toDateString(),
                'Amount'            => $totalCost,
                'TaxAmount'         => 0,
                'BranchID'          => $transfer->FromBranch,
                'DepartmentID'      => null,
                'CurrencyID'        => 56,
                'CurrencyCode'      => 'KES',
                'ExchangeRate'      => 1,
                'Narration'         => 'Transfer of inventory to branch ' . $transfer->ToBranch . ' via ' . $transfer->TransferId,
                'SourceTable'       => 't_Transfers',
                'SystemDescription' => 'Inventory Transfer ' . $transfer->TransferId,
            ];

            $result = $svc->postFromTypeMapping($payload);

            activity()->performedOn($transfer)->causedBy($user)
                ->withProperties(['financial_post' => $result])
                ->log('Financial transaction posted for transfer');
        } else {
            activity()->performedOn($transfer)->causedBy($user)
                ->log('Transfer approved with $0 total cost, no financial transaction posted');
        }

        activity()->performedOn($transfer)->causedBy($user)
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Approved Transaction Transfer: stock deducted and transaction recorded');

        DB::commit();
    } catch (Throwable $th) {
        DB::rollBack();
        throw $th;
    }
}

    public function reject(int $id, string $comments = null): void
    {
        try {
            $transfer = TransactionTransfer::findOrFail($id);
            $user = Auth::user();

            // Use workflow to reject
            $this->workflow->reject($transfer, $user, Transfers::Rejected, $comments ?? 'Transfer Rejected', 'Status');

            // Update transfer status to Rejected
            $transfer->Status = Transfers::Rejected->value;
            $transfer->ModifiedBy = $user->Id;
            $transfer->ModifiedOn = now();
            $transfer->save();

            activity()->performedOn($transfer)->causedBy($user)
                ->withProperties(['attributes' => $transfer->toArray()])
                ->log('Rejected Transaction Transfer');
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function getApprovedTransfers()
    {
        return TransactionTransfer::where('Status', Transfers::InTransit->value)
            ->orderByDesc('CreatedOn')
            ->get(['Id', 'TransferId', 'TransferDate', 'FromBranch', 'ToBranch']);
    }
}