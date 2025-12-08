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
use Illuminate\Support\Facades\Log;
use App\Models\Inventory\StockTransaction;
use App\Services\Workflow\ApprovalWorkflow;
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
    
    protected function generateTransferId(TransactionTransfer $transfer): string
    {
        $year = now()->format('Y');
        return 'TRF-' . $year . '-' . str_pad($transfer->Id, 4, '0', STR_PAD_LEFT);
    }

    public function createTransfer(array $data): TransactionTransfer
    {
        Log::info('Creating new transfer', $data);

        $pendingStatusId = CodeDetail::where('CodeID', 'TransferStatus')->where('Description', 'Pending')->value('ID');
        $inTransitStatusId = CodeDetail::where('CodeID', 'TransferStatus')->where('Description', 'In Transit')->value('ID');

        $data['Status'] = $pendingStatusId ?? $data['Status'] ?? null;

        if ($data['RequisitionType'] === 'procurement') {
            $requisition = GoodsReceipt::findOrFail($data['RequisitionId']);
            $fromBranch = $this->getHQBranchId();
            $toBranch = $data['ToBranch']; 
        } else {
            $requisition = \App\Models\Inventory\InterBranchRequisition::findOrFail($data['RequisitionId']);
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
            'Status' => $pendingStatusId,
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ]);
        $transfer->save();

        $transfer->TransferId = $this->generateTransferId($transfer);
        $transfer->save();

        Log::info('Transfer created successfully', [
            'transfer_id' => $transfer->Id,
            'transfer_number' => $transfer->TransferId,
            'status' => $transfer->Status
        ]);

        try {
            // Create workflow instance and submit for approval - SAME PATTERN AS INTERBRANCH
            $transferflow = new ApprovalWorkflow('TransferStatus', 'Status');
            $transferflow->submit(
                $transfer,
                Auth::user(),
                Transfers::Pending,
                'Transaction Transfer Submitted for Approval'
            );

            Log::info('Workflow submitted for transfer', [
                'transfer_id' => $transfer->Id,
                'user_id' => Auth::id(),
                'status_id' => $transfer->Status,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to submit workflow for transfer', [
                'transfer_id' => $transfer->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        activity()->performedOn($transfer)->causedBy(Auth::user())
            ->withProperties(['attributes' => $transfer->toArray()])
            ->log('Created Transaction Transfer');

        return $transfer;
    }
    
    public function createTransferItems(TransactionTransfer $transfer, array $items): void
    {
        Log::info('Creating transfer items', [
            'transfer_id' => $transfer->Id,
            'item_count' => count($items),
            'user_id' => Auth::id()
        ]);

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
        Log::info('Updating transfer', [
            'transfer_id' => $transfer->Id,
            'data_keys' => array_keys($data),
            'user_id' => Auth::id()
        ]);

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
        Log::info('Starting transfer approval', [
            'transfer_id' => $id,
            'user_id' => Auth::id(),
            'comments' => $comments
        ]);

        DB::beginTransaction();

        try {
            $transfer = TransactionTransfer::with('items')->findOrFail($id);
            $user = Auth::user();

            Log::info('Found transfer for approval', [
                'transfer_id' => $transfer->Id,
                'current_status' => $transfer->Status,
                'user_id' => $user->Id
            ]);

            // Create workflow instance and approve - SAME PATTERN AS INTERBRANCH
            $workflow = new ApprovalWorkflow('TransferStatus', 'Status');
            
            // Use workflow to approve - SAME PATTERN AS INTERBRANCH
            $workflow->approve($transfer, $user, Transfers::InTransit, $comments ?? 'Transfer Approved');

            Log::info('Workflow approval completed successfully');

            // Update transfer status to In Transit
            $inTransitStatusId = CodeDetail::where('CodeID', 'TransferStatus')->where('Description', 'In Transit')->value('ID');
            $transfer->Status = $inTransitStatusId;
            $transfer->ModifiedBy = $user->Id;
            $transfer->ModifiedOn = now();
            $transfer->save();

            Log::info('Transfer status updated', [
                'transfer_id' => $transfer->Id,
                'new_status' => $transfer->Status
            ]);

            // Process stock deduction and inventory hold
            Log::info('Processing stock transactions', [
                'total_items' => $transfer->items->count(),
                'from_branch' => $transfer->FromBranch,
                'to_branch' => $transfer->ToBranch
            ]);

            foreach ($transfer->items as $item) {
                // Check stock availability
                $stockFrom = StockItem::where('ItemID', $item->Item)
                    ->where('Branch', $transfer->FromBranch)
                    ->first();

                if (!$stockFrom) {
                    $errorMsg = "No stock found for Item {$item->Item} in branch {$transfer->FromBranch}";
                    Log::error('Stock not found', [
                        'item_id' => $item->Item,
                        'branch' => $transfer->FromBranch
                    ]);
                    throw new Exception($errorMsg);
                }

                if ($stockFrom->CurrentQty < $item->DispatchedQty) {
                    $errorMsg = "Insufficient stock for Item {$item->Item}. Available: {$stockFrom->CurrentQty}, Required: {$item->DispatchedQty}";
                    Log::error('Insufficient stock', [
                        'item_id' => $item->Item,
                        'available' => $stockFrom->CurrentQty,
                        'required' => $item->DispatchedQty
                    ]);
                    throw new Exception($errorMsg);
                }

                // Get last balance
                $lastBalance = StockTransaction::where('ItemID', $item->Item)
                    ->where('BranchID', $transfer->FromBranch)
                    ->orderByDesc('TransactionDate')
                    ->orderByDesc('id')
                    ->value('BalanceQty');

                if ($lastBalance === null) {
                    $lastBalance = $stockFrom->CurrentQty ?? 0;
                }

                $dispatchedQty = $item->DispatchedQty;
                $newBalance = $lastBalance - $dispatchedQty;
                $totalCost = ($item->UnitCost ?? 0) * $dispatchedQty * -1;

                // Generate SKU ID
                $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                    ->orderByDesc('id')
                    ->value('SKUID');

                $nextNumber = $latestSKU
                    ? str_pad(((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1, 3, '0', STR_PAD_LEFT)
                    : '001';

                $skuId = 'SKU' . $nextNumber;

                // Get transaction type ID
                $transactionTypeId = CodeDetail::where('CodeID', 'Source')
                    ->where('Description', 'Transaction Transfer')->value('ID');

                StockTransaction::create([
                    'SKUID' => $skuId,
                    'TransactionType' => $transactionTypeId,
                    'ItemID' => $item->Item,
                    'StoreID' => $stockFrom->Store ?? null,
                    'BranchID' => $transfer->FromBranch,
                    'UnitCost' => $item->UnitCost,
                    'UOMID' => $item->uom->Id ?? null,
                    'QuantityIn' => 0,
                    'QuantityOut' => $dispatchedQty,
                    'BalanceQty' => $newBalance,
                    'TotalCost' => $totalCost,
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
                    'Status' => $inTransitStatusId,
                    'Remarks' => $item->Remarks,
                    'CreatedBy' => $user->Id,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $user->Id,
                    'ModifiedOn' => now(),
                ]);
            }

            activity()->performedOn($transfer)->causedBy($user)
                ->withProperties(['attributes' => $transfer->toArray()])
                ->log('Approved Transaction Transfer: stock deducted and transaction recorded');

            DB::commit();
            
            Log::info('Transfer approved successfully', [
                'transfer_id' => $transfer->Id,
                'user_id' => $user->Id
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Transfer approval failed: ' . $th->getMessage(), [
                'transfer_id' => $id,
                'user_id' => Auth::id(),
                'exception' => $th,
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    public function reject(int $id, string $comments = null): void
    {
        Log::info('Starting transfer rejection', [
            'transfer_id' => $id,
            'user_id' => Auth::id(),
            'comments' => $comments
        ]);

        try {
            $transfer = TransactionTransfer::findOrFail($id);
            $user = Auth::user();

            Log::info('Found transfer for rejection', [
                'transfer_id' => $transfer->Id,
                'current_status' => $transfer->Status,
                'user_id' => $user->Id
            ]);

            // Create workflow instance and reject - SAME PATTERN AS INTERBRANCH
            $workflow = new ApprovalWorkflow('TransferStatus', 'Status');
            
            // Use workflow to reject - SAME PATTERN AS INTERBRANCH
            $workflow->reject($transfer, $user, Transfers::Rejected, $comments ?? 'Rejected via UI');

            Log::info('Workflow rejection completed successfully');

            // Update transfer status to Rejected
            $rejectedStatusId = CodeDetail::where('CodeID', 'TransferStatus')->where('Description', 'Rejected')->value('ID');
            $transfer->Status = $rejectedStatusId;
            $transfer->ModifiedBy = $user->Id;
            $transfer->ModifiedOn = now();
            $transfer->save();

            activity()->performedOn($transfer)->causedBy($user)
                ->withProperties(['attributes' => $transfer->toArray()])
                ->log('Rejected Transaction Transfer');
            
            Log::info('Transfer rejected successfully', [
                'transfer_id' => $transfer->Id,
                'new_status' => $transfer->Status
            ]);
        } catch (Throwable $th) {
            Log::error('Transfer rejection failed: ' . $th->getMessage(), [
                'transfer_id' => $id,
                'user_id' => Auth::id(),
                'exception' => $th,
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    public function getApprovedTransfers()
    {
        $inTransitId = CodeDetail::where('CodeID', 'TransferStatus')->where('Description', 'In Transit')->value('ID');

        return TransactionTransfer::where('Status', $inTransitId)
            ->orderByDesc('CreatedOn')
            ->get(['Id', 'TransferId', 'TransferDate', 'FromBranch', 'ToBranch']);
    }
}