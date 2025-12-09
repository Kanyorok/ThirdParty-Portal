<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockAdjustmentItem;
use App\Models\Inventory\StockItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory\StockTransaction;
use App\Services\Workflow\ApprovalWorkflow;
use Exception;
use Throwable;

class StockAdjustmentService
{
    protected ApprovalWorkflow $workflow;

    public function __construct(ApprovalWorkflow $workflow)
    {
                $this->workflow = new ApprovalWorkflow('TransferStatus','Status');
    }

    public function create(array $validated): void
    {
        Log::info('=== StockAdjustmentService::create START ===', [
            'data_keys' => array_keys($validated),
            'user_id' => Auth::id()
        ]);

        DB::beginTransaction();

        try {
            $adjustment = StockAdjustment::create([
                'AdjustmentDate' => $validated['AdjustmentDate'],
                'Branch' => $validated['Branch'],
                'AdjustedBy' => $validated['AdjustedBy'],
                'Status' => Transfers::Pending->value,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            $adjustment->AdjustmentId = 'SA/' . now()->format('Ymd') . '/' . str_pad($adjustment->Id, 4, '0', STR_PAD_LEFT);
            $adjustment->save();

            foreach ($validated['items'] as $item) {
                StockAdjustmentItem::create([
                    'AdjustmentId' => $adjustment->Id,
                    'Item' => $item['Item'],
                    'UOM' => $item['UOM'],
                    'UnitCost' => $item['UnitCost'] ?? null,
                    'Reason' => $item['Reason'],
                    'AdjustmentQty' => $item['AdjustmentQty'],
                    'Remarks' => $item['Remarks'] ?? null,
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);
            }

            // Create workflow instance and submit for approval
            $this->workflow->submit(
                $adjustment,
                Auth::user(),
                Transfers::Pending,
                'Stock Adjustment Submitted for Approval'
            );

            Log::info('Workflow submitted for stock adjustment', [
                'adjustment_id' => $adjustment->Id,
                'user_id' => Auth::id(),
                'status' => $adjustment->Status,
            ]);

            activity()->performedOn($adjustment)->causedBy(Auth::user())
                ->withProperties(['attributes' => $adjustment->toArray()])
                ->log('Created Stock Adjustment');

            DB::commit();

            Log::info('=== StockAdjustmentService::create SUCCESS ===', [
                'adjustment_id' => $adjustment->Id,
                'adjustment_number' => $adjustment->AdjustmentId,
                'status' => $adjustment->Status
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('=== StockAdjustmentService::create FAILED ===', [
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'exception' => $th,
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    public function update(StockAdjustment $adjustment, array $validated): void
    {
        Log::info('=== StockAdjustmentService::update START ===', [
            'adjustment_id' => $adjustment->Id,
            'data_keys' => array_keys($validated),
            'user_id' => Auth::id()
        ]);

        DB::transaction(function () use ($adjustment, $validated) {
            $adjustment->fill([
                'AdjustmentDate' => $validated['AdjustmentDate'],
                'Branch' => $validated['Branch'],
                'AdjustedBy' => $validated['AdjustedBy'],
                'Status' => Transfers::Pending->value,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ])->save();

            $existingItemIds = $adjustment->items()->pluck('Item')->toArray();
            $incomingItems = collect($validated['items']);
            $incomingItemIds = $incomingItems->pluck('Item')->toArray();

            $itemsToDelete = array_diff($existingItemIds, $incomingItemIds);
            if (!empty($itemsToDelete)) {
                $adjustment->items()->whereIn('Item', $itemsToDelete)->delete();
                Log::info("Deleted items from adjustment ID {$adjustment->Id}: " . implode(', ', $itemsToDelete));
            }

            foreach ($incomingItems as $itemData) {
                $item = $adjustment->items()->where('Item', $itemData['Item'])->first();

                if ($item) {
                    $item->update([
                        'Reason' => $itemData['Reason'],
                        'AdjustmentQty' => $itemData['AdjustmentQty'],
                        'Remarks' => $itemData['Remarks'] ?? null,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                } else {
                    StockAdjustmentItem::create([
                        'AdjustmentId' => $adjustment->Id,
                        'Item' => $itemData['Item'],
                        'UOM' => $itemData['UOM'],
                        'UnitCost' => $itemData['UnitCost'] ?? null,
                        'Reason' => $itemData['Reason'],
                        'AdjustmentQty' => $itemData['AdjustmentQty'],
                        'Remarks' => $itemData['Remarks'] ?? null,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                }
            }

            // Reset workflow if needed
            if ($adjustment->Status != Transfers::Pending->value) {
                $this->workflow->submit(
                    $adjustment,
                    Auth::user(),
                    Transfers::Pending,
                    'Stock Adjustment Updated - Resubmitted for Approval'
                );
            }

            activity()->performedOn($adjustment)->causedBy(Auth::user())
                ->withProperties(['attributes' => $validated])
                ->log('Updated Stock Adjustment');

            Log::info('=== StockAdjustmentService::update SUCCESS ===', [
                'adjustment_id' => $adjustment->Id
            ]);
        });
    }

    public function approve(int $adjustmentId, string $comments = null): void
    {
        Log::info('=== StockAdjustmentService::approve START ===', [
            'adjustment_id' => $adjustmentId,
            'user_id' => Auth::id(),
            'comments' => $comments
        ]);

        DB::beginTransaction();

        try {
            $adjustment = StockAdjustment::with('items')->findOrFail($adjustmentId);
            $user = Auth::user();

            Log::info('Found adjustment for approval', [
                'adjustment_id' => $adjustment->Id,
                'current_status' => $adjustment->Status,
                'user_id' => $user->Id
            ]);

            // Use workflow to approve
            $this->workflow->approve($adjustment, $user, Transfers::Approved, $comments ?? 'Stock Adjustment Approved');

            Log::info('Workflow approval completed successfully');

            // Update adjustment status
            $adjustment->update([
                'Status' => Transfers::Approved->value,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            Log::info('Adjustment status updated', [
                'adjustment_id' => $adjustment->Id,
                'new_status' => $adjustment->Status
            ]);

            // Process stock adjustments
            Log::info('Processing stock adjustments', [
                'total_items' => $adjustment->items->count(),
                'branch' => $adjustment->Branch
            ]);

            foreach ($adjustment->items as $item) {
                // Update stock item quantity
                $stockItem = StockItem::firstOrNew([
                    'ItemID' => $item->Item,
                    'Branch' => $adjustment->Branch,
                ]);
                
                $currentQty = $stockItem->CurrentQty ?? 0;
                $newQty = $currentQty + $item->AdjustmentQty;
                
                $stockItem->CurrentQty = $newQty;
                $stockItem->ModifiedBy = $user->Id;
                $stockItem->ModifiedOn = now();
                $stockItem->save();

                // Create inventory hold for stock-out adjustments (negative quantity)
                if ($item->AdjustmentQty < 0) {
                    $reasonDesc = CodeDetail::where('ID', $item->Reason)->value('Description');
                    
                    if (strtolower($reasonDesc) !== 'stock found') {
                        $sourceCodeId = CodeDetail::where('CodeID', 'Source')
                            ->where('Description', 'Stock Adjustment')
                            ->value('ID');

                        InventoryHold::create([
                            'ItemID' => $item->Item,
                            'BranchID' => $adjustment->Branch,
                            'Quantity' => abs($item->AdjustmentQty),
                            'Reason' => $item->Reason,
                            'Source' => $sourceCodeId,
                            'SourceID' => $adjustment->Id,
                            'Status' => Transfers::UnderReview->value,
                            'Remarks' => $item->Remarks,
                            'CreatedBy' => $user->Id,
                            'CreatedOn' => now(),
                            'ModifiedBy' => $user->Id,
                            'ModifiedOn' => now(),
                        ]);
                    }
                }

                // Generate SKU ID
                $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                    ->orderByDesc('id')
                    ->value('SKUID');

                $nextNumber = $latestSKU
                    ? ((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1
                    : 1;

                $skuId = 'SKU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                // Get transaction type ID
                $adjustmentTypeId = CodeDetail::where('CodeID', 'Source')
                    ->where('Description', 'Stock Adjustment')
                    ->value('ID');

                // Calculate quantities
                $qty = $item->AdjustmentQty;
                $quantityIn = $qty > 0 ? $qty : 0;
                $quantityOut = $qty < 0 ? abs($qty) : 0;

                // Get last balance
                $lastBalance = StockTransaction::where('ItemID', $item->Item)
                    ->where('BranchID', $adjustment->Branch)
                    ->orderByDesc('TransactionDate')
                    ->orderByDesc('id')
                    ->value('BalanceQty');

                if ($lastBalance === null) {
                    $lastBalance = $currentQty ?? 0;
                }

                $newBalance = $lastBalance + $quantityIn - $quantityOut;

                // Calculate total cost
                $adjustmentQty = $quantityIn > 0 ? $quantityIn : $quantityOut;
                $totalCost = ($item->UnitCost ?? 0) * $adjustmentQty;

                // If it's a stock-out, make total cost negative
                if ($quantityOut > 0) {
                    $totalCost *= -1;
                }

                // Create stock transaction
                StockTransaction::create([
                    'SKUID' => $skuId,
                    'TransactionType' => $adjustmentTypeId,
                    'ReferenceID' => $adjustment->Id,
                    'ItemID' => $item->Item,
                    'StoreID' => null,
                    'BranchID' => $adjustment->Branch,
                    'UnitCost' => $item->UnitCost ?? null,
                    'UOMID' => $item->UOM,
                    'QuantityIn' => $quantityIn,
                    'QuantityOut' => $quantityOut,
                    'BalanceQty' => $newBalance,
                    'TotalCost' => $totalCost,
                    'TransactionDate' => now(),
                    'Remarks' => 'Stock Adjustment within Branch ID ' . ($adjustment->Branch ?? 'Unknown'),
                    'CreatedBy' => $user->Id,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $user->Id,
                    'ModifiedOn' => now(),
                ]);

                Log::info('Stock transaction recorded for adjustment', [
                    'ItemID' => $item->Item,
                    'BranchID' => $adjustment->Branch,
                    'AdjustmentQty' => $item->AdjustmentQty,
                    'TransactionType' => 'Stock Adjustment',
                    'ReferenceID' => $adjustment->Id,
                ]);
            }

            activity()->performedOn($adjustment)->causedBy($user)
                ->withProperties(['attributes' => $adjustment->toArray()])
                ->log('Approved Stock Adjustment: stock adjusted and transaction recorded');

            DB::commit();
            
            Log::info('=== StockAdjustmentService::approve SUCCESS ===', [
                'adjustment_id' => $adjustment->Id,
                'user_id' => $user->Id
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('=== StockAdjustmentService::approve FAILED ===', [
                'adjustment_id' => $adjustmentId,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'exception' => $th,
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    public function reject(int $adjustmentId, string $comments = null): void
    {
        Log::info('=== StockAdjustmentService::reject START ===', [
            'adjustment_id' => $adjustmentId,
            'user_id' => Auth::id(),
            'comments' => $comments
        ]);

        try {
            $adjustment = StockAdjustment::findOrFail($adjustmentId);
            $user = Auth::user();

            Log::info('Found adjustment for rejection', [
                'adjustment_id' => $adjustment->Id,
                'current_status' => $adjustment->Status,
                'user_id' => $user->Id
            ]);

            // Use workflow to reject
            $this->workflow->reject($adjustment, $user, Transfers::Rejected, $comments ?? 'Stock Adjustment Rejected');

            Log::info('Workflow rejection completed successfully');

            // Update adjustment status
            $adjustment->update([
                'Status' => Transfers::Rejected->value,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);

            activity()->performedOn($adjustment)->causedBy($user)
                ->withProperties(['attributes' => $adjustment->toArray()])
                ->log('Rejected Stock Adjustment');
            
            Log::info('=== StockAdjustmentService::reject SUCCESS ===', [
                'adjustment_id' => $adjustment->Id,
                'new_status' => $adjustment->Status
            ]);
        } catch (Throwable $th) {
            Log::error('=== StockAdjustmentService::reject FAILED ===', [
                'adjustment_id' => $adjustmentId,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'exception' => $th,
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    public function getApprovedAdjustments()
    {
        return StockAdjustment::where('Status', Transfers::Approved->value)
            ->orderByDesc('CreatedOn')
            ->get(['Id', 'AdjustmentId', 'AdjustmentDate', 'Branch', 'AdjustedBy']);
    }
}