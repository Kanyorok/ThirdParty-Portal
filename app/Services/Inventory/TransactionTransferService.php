<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Branch;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockGRNLedger;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\Store;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\Requisitions;
use App\Services\Finance\TransactionService;
use App\Services\Workflow\ApprovalWorkflow;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionTransferService
{
    protected ApprovalWorkflow $workflow;
    protected TransactionService $transactionService;

    public function __construct(
        ApprovalWorkflow $workflow,
        TransactionService $transactionService
    ) {
        $this->workflow = new ApprovalWorkflow('TransferStatus', 'Status');
        $this->transactionService = $transactionService;
    }

    public function getHQBranchId(): int
    {
        $hqBranch = Branch::where('IsHQ', 1)->first();
        if (! $hqBranch) {
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
            $requisition = Requisitions::findOrFail($data['RequisitionId']);
            $fromBranch = $data['FromBranch'];
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
            'TransferDate'   => $data['TransferDate'],
            'TransferredBy'  => $data['TransferredBy'],
            'RequisitionId'  => $data['RequisitionId'],
            'FromBranch'     => $fromBranch,
            'ToBranch'       => $toBranch,
            'RequisitionType'=> $data['RequisitionType'],
            'Status'         => Transfers::Pending->value,
            'CreatedBy'      => Auth::id(),
            'ModifiedBy'     => Auth::id(),
            'CreatedOn'      => now(),
            'ModifiedOn'     => now(),
        ]);
        $transfer->save();

        $transfer->TransferID = $this->generateTransferId($transfer);
        $transfer->save();

        try {
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
            $batchAllocation = $itemData['batch_allocation'] ?? null;
            $fromBranch = $transfer->FromBranch;

            if (! $this->branchHasGRNLedger($fromBranch, $itemId)) {
                throw new Exception("Branch {$fromBranch} has no GRN ledger entries for item {$itemId}. Cannot transfer without GRN tracking.");
            }

            $created = TransactionTransferItem::create([
                'TransferId' => $transfer->Id,
                'Item' => $itemId,
                'ApprovedQty' => $itemData['approved_qty'],
                'UnitCost' => $itemData['unit_cost'] ?? null,
                'UOM' => $itemData['uom'],
                'DispatchedQty' => $dispatchedQty,
                'BatchAllocation' => $batchAllocation ? json_encode($batchAllocation) : null,
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

    private function branchHasGRNLedger($branchId, $itemId): bool
    {
        $store = $this->getDefaultStoreForBranch($branchId);
        if (! $store) {
            return false;
        }
        

        return StockGRNLedger::where('ItemNo', $itemId)
            ->where('Branch', $branchId)
            ->where('Store', $store->Id)
            ->where('RemainingQTY', '>', 0)
            ->exists();
            
    }

    public function getAvailableGRNBatches($itemId, $branchId, $storeId = null)
    {
        if (! $storeId) {
            $store = $this->getDefaultStoreForBranch($branchId);
            $storeId = $store ? $store->Id : null;
        }

        if (! $storeId) {
            throw new Exception("No store found for branch {$branchId}");
        }

        $batches = StockGRNLedger::with(['parentLedger', 'goodsReceipt'])
            ->where('ItemNo', $itemId)
            ->where('Branch', $branchId)
            ->where('Store', $storeId)
            ->where('RemainingQTY', '>', 0)
            ->orderBy('ReceivedDate', 'asc')
            ->orderBy('CreatedOn', 'asc')
            ->get()
            ->map(function ($batch) {
                return [
                    'ledger_id' => $batch->Id,
                    'grn_id' => $batch->GRNID,
                    'goods_receipt_id' => $batch->GoodsReceiptId,
                    'received_date' => \Carbon\Carbon::parse($batch->ReceivedDate)->format('Y-m-d'),
                    'unit_price' => (float) $batch->UnitPrice,
                    'remaining_qty' => (float) $batch->RemainingQTY,
                    'total_value' => (float) ($batch->RemainingQTY * $batch->UnitPrice),
                    'age_days' => now()->diffInDays(\Carbon\Carbon::parse($batch->ReceivedDate)),
                    'source_type' => $batch->SourceType ?? 'procurement',
                    'parent_ledger_id' => $batch->ParentLedgerId,
                    'original_grn_id' => $batch->parentLedger ? $batch->parentLedger->GRNID : $batch->GRNID,
                    'is_transfer' => $batch->SourceType === 'transfer',
                ];
            });

        return $batches;
    }

    public function approve(int $id, string $comments = null): void
    {
        DB::beginTransaction();

        try {
            $transfer = TransactionTransfer::with('items')->findOrFail($id);

            $user = Auth::user();
            $isHQ = $user->branch && $user->branch->IsHQ;

            foreach ($transfer->items as $item) {
                if (! $this->branchHasGRNLedger($transfer->FromBranch, $item->Item)) {
                    throw new Exception("Item {$item->Item} has no GRN ledger entries in branch {$transfer->FromBranch}. Cannot transfer without GRN tracking.");
                }
            }

            $this->workflow->approve(
                $transfer,
                $user,
                Transfers::InTransit,
                $comments ?? 'Transfer Approved',
                'Status'
            );

            $transfer->Status = Transfers::InTransit->value;
            $transfer->ModifiedBy = $user->Id;
            $transfer->ModifiedOn = now();
            $transfer->save();

            $totalCost = 0;

            foreach ($transfer->items as $item) {
                $sourceStore = $this->getDefaultStoreForBranch($transfer->FromBranch);
                $stockFrom = StockItem::where('ItemID', $item->Item)
                    ->where('Store', $sourceStore->Id)
                    ->where('Branch', $transfer->FromBranch)
                    ->first();

                if (! $stockFrom) {
                    throw new Exception("No stock found for Item {$item->Item} in branch {$transfer->FromBranch}");
                }

                if ($stockFrom->CurrentQty < $item->DispatchedQty) {
                    throw new Exception("Insufficient stock for Item {$item->Item}. Available: {$stockFrom->CurrentQty}, Required: {$item->DispatchedQty}");
                }

                $allocations = [];
                $remainingQty = $item->DispatchedQty;

                $availableBatches = StockGRNLedger::where('ItemNo', $item->Item)
                    ->where('Store', $sourceStore->Id)
                    ->where('Branch', $transfer->FromBranch)
                    ->where('RemainingQTY', '>', 0)
                    ->orderBy('ReceivedDate', 'asc')
                    ->orderBy('CreatedOn', 'asc')
                    ->orderBy('Id', 'asc')
                    ->get();

                $totalAvailable = (float) $availableBatches->sum(fn ($b) => (float) $b->RemainingQTY);
                if ($totalAvailable < (float) $item->DispatchedQty) {
                    throw new Exception("Insufficient GRN batches for Item {$item->Item}. Available: {$totalAvailable}, Required: {$item->DispatchedQty}");
                }

                if (! empty($item->BatchAllocation) && ! $isHQ) {
                    $userAllocations = json_decode($item->BatchAllocation, true);

                    foreach ($userAllocations as $userAlloc) {
                        $batch = StockGRNLedger::where('Id', $userAlloc['ledger_id'])
                            ->where('ItemNo', $item->Item)
                            ->where('Store', $sourceStore->Id)
                            ->where('Branch', $transfer->FromBranch)
                            ->first();

                        if (! $batch) {
                            throw new Exception("Selected GRN batch not found: {$userAlloc['grn_id']}");
                        }

                        if ((float)$batch->RemainingQTY < (float)$userAlloc['quantity']) {
                            throw new Exception("Insufficient quantity in GRN {$userAlloc['grn_id']}. Available: {$batch->RemainingQTY}, Requested: {$userAlloc['quantity']}");
                        }

                        $allocQty = (float) $userAlloc['quantity'];

                        $allocations[] = [
                            'ledger_id' => $batch->Id,
                            'quantity' => $allocQty,
                        ];

                        $batch->RemainingQTY = (float)$batch->RemainingQTY - $allocQty;
                        $batch->save();

                        $remainingQty -= $allocQty;
                    }
                } else {
                    foreach ($availableBatches as $batch) {
                        if ($remainingQty <= 0) {
                            break;
                        }

                        $batchRemaining = (float) $batch->RemainingQTY;
                        if ($batchRemaining <= 0) {
                            continue;
                        }

                        $allocatedQty = min($batchRemaining, $remainingQty);

                        $allocations[] = [
                            'ledger_id' => $batch->Id,
                            'quantity' => $allocatedQty,
                        ];

                        $batch->RemainingQTY = $batchRemaining - $allocatedQty;
                        $batch->save();

                        $remainingQty -= $allocatedQty;
                    }
                }

                $itemCost = 0;
                foreach ($allocations as $allocation) {
                    $ledger = StockGRNLedger::find($allocation['ledger_id']);
                    $unitPrice = $ledger ? (float) $ledger->UnitPrice : 0;
                    $itemCost += $unitPrice * $allocation['quantity'];
                }
                $totalCost += $itemCost;

                $item->BatchAllocation = json_encode($allocations);
                $item->UnitCost = $itemCost / max($item->DispatchedQty, 1);
                $item->save();

                $stockFrom->CurrentQty -= $item->DispatchedQty;
                $stockFrom->ModifiedBy = $user->Id;
                $stockFrom->ModifiedOn = now();
                $stockFrom->save();

                $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                    ->orderByDesc('id')
                    ->value('SKUID');

                $nextNumber = $latestSKU
                    ? str_pad(((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1, 3, '0', STR_PAD_LEFT)
                    : '001';

                $skuId = 'SKU' . $nextNumber;

                $transactionTypeId = CodeDetail::where('CodeID', 'Source')
                    ->where('Description', 'Transaction Transfer')->value('ID');

                if (! $transactionTypeId) {
                    throw new Exception("Transaction type not found for 'Transaction Transfer' in CodeDetail");
                }

                $lastBalance = StockTransaction::where('ItemID', $item->Item)
                    ->where('BranchID', $transfer->FromBranch)
                    ->orderByDesc('TransactionDate')
                    ->orderByDesc('id')
                    ->value('BalanceQty') ?? $stockFrom->CurrentQty ?? 0;

                $newBalance = $lastBalance - $item->DispatchedQty;

                StockTransaction::create([
                    'SKUID' => $skuId,
                    'TransactionType' => $transactionTypeId,
                    'ItemID' => $item->Item,
                    'StoreID' => $sourceStore->Id,
                    'BranchID' => $transfer->FromBranch,
                    'UnitCost' => $item->DispatchedQty > 0 ? $itemCost / $item->DispatchedQty : 0,
                    'UOMID' => $item->uom->Id ?? null,
                    'QuantityIn' => 0,
                    'QuantityOut' => $item->DispatchedQty,
                    'BalanceQty' => $newBalance,
                    'TotalCost' => $itemCost * -1,
                    'TransactionDate' => now(),
                    'ReferenceID' => $transfer->Id,
                    'Remarks' => 'Transfer to Branch ID ' . $transfer->ToBranch .
                                ($isHQ ? ' using FIFO' : ' using selected GRN batches') .
                                ' | Allocations: ' . collect($allocations)->map(function ($a) {
                                    $ledger = StockGRNLedger::find($a['ledger_id']);

                                    return ($ledger ? $ledger->GRNID : 'GRN-' . $a['ledger_id']) . ' (' . $a['quantity'] . ')';
                                })->implode(', '),
                    'CreatedBy' => $user->Id,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $user->Id,
                    'ModifiedOn' => now(),
                ]);

                $reasonId = CodeDetail::where('CodeID', 'AdjustmentReason')
                    ->where('Description', 'In Transit')->value('ID');
                $sourceId = CodeDetail::where('CodeID', 'Source')
                    ->where('Description', 'Transaction Transfer')->value('ID');

                if (! $reasonId || ! $sourceId) {
                    throw new Exception("Required CodeDetails not found - Reason: {$reasonId}, Source: {$sourceId}");
                }

                InventoryHold::create([
                    'ItemID' => $item->Item,
                    'BranchID' => $transfer->ToBranch,
                    'Quantity' => $item->DispatchedQty,
                    'Reason' => $reasonId,
                    'Source' => $sourceId,
                    'SourceID' => $transfer->Id,
                    'Status' => Transfers::InTransit->value,
                    'Remarks' => $item->Remarks . ' | Allocations: ' .
                                collect($allocations)->map(function ($a) {
                                    $ledger = StockGRNLedger::find($a['ledger_id']);

                                    return ($ledger ? $ledger->GRNID : 'GRN-' . $a['ledger_id']) . ' (' . $a['quantity'] . ')';
                                })->implode(', '),
                    'CreatedBy' => $user->Id,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $user->Id,
                    'ModifiedOn' => now(),
                ]);
            }

            if ($totalCost > 0) {
                $payload = [
                    'ModuleID' => 400000,
                    'TransactionTypeID' => 5,
                    'TransactionType' => 'InterBranch Inventory Transfer',
                    'ReferenceNumber' => $transfer->TransferID,
                    'TransactionDate' => now()->toDateString(),
                    'Amount' => $totalCost,
                    'TaxAmount' => 0,
                    'BranchID' => $transfer->FromBranch,
                    'DepartmentID' => null,
                    'CurrencyID' => 56,
                    'CurrencyCode' => 'KES',
                    'ExchangeRate' => 1,
                    'Narration' => 'Transfer of inventory to branch ' . $transfer->ToBranch .
                                           ' using ' . ($isHQ ? 'FIFO' : 'selected GRN batches') .
                                           '. Transfer ID: ' . $transfer->TransferID,
                    'SourceTable' => 't_Transfers',
                    'SystemDescription' => 'Inventory Transfer ' . $transfer->TransferID,
                ];

                try {
                    $result = $this->transactionService->postFromTypeMapping($payload);
                } catch (Exception $fe) {
                    throw new Exception("Finance transaction failed: " . $fe->getMessage());
                }
            }

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    protected function getDefaultStoreForBranch($branchId)
    {
        $store = Store::where('BranchID', $branchId)
            ->where('Status', true)
            ->where('IsMainStore', true)
            ->whereNull('DeletedOn')
            ->first();
            
            

        if (! $store) {
            throw new Exception("No active store found for branch {$branchId}");
        }

        return $store;
    }

    public function reject(int $id, string $comments = null): void
    {
        try {
            $transfer = TransactionTransfer::findOrFail($id);
            $user = Auth::user();

            $this->workflow->reject($transfer, $user, Transfers::Rejected, $comments ?? 'Transfer Rejected', 'Status');

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
            ->get(['Id', 'TransferID', 'TransferDate', 'FromBranch', 'ToBranch']);
    }
}