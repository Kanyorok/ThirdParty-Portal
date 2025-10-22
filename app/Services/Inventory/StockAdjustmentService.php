<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\Transfers;
use App\Models\Core\CodeDetail;
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
use App\Models\Auth\User;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Models\Inventory\InventoryHoldReview;
use Illuminate\Support\Facades\Activity;

class StockAdjustmentService
{
    public function create(array $validated, $user)
    {
        DB::transaction(function () use ($validated, $user) {
            $adjustment = StockAdjustment::create([
                'AdjustmentDate' => $validated['AdjustmentDate'],
                'Branch' => $validated['Branch'],
                'AdjustedBy' => $validated['AdjustedBy'],
                'Status' => Transfers::Pending->value,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedBy' => auth()->id(),
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
                    'CreatedBy' => auth()->id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                ]);
            }


                Workflow::create([
                'Source' => 'StockAdjustment',
                'SourceID' => $adjustment->Id,
                'Stage' => Transfers::Pending->label(),
                'Status' => Transfers::Pending->value,
                'Notes' => 'Adjustment submitted, awaiting approval',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);


            PendingWorkflow::updateOrCreate(
                ['Source' => 'StockAdjustment', 'SourceID' => $adjustment->Id],
                [
                    'Stage' => Transfers::Pending->label(),
                    'UserId' => $user->id,
                    'CreatedBy' => auth()->id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                ]
            );
        });
    }

    public function update(StockAdjustment $adjustment, array $validated)
    {
        Log::info('StockAdjustmentService@update: Updating Stock Adjustment ID:', ['id' => $adjustment->Id]);

        DB::transaction(function () use ($adjustment, $validated) {
            $adjustment->fill([
                'AdjustmentDate' => $validated['AdjustmentDate'],
                'Branch' => $validated['Branch'],
                'Status' => Transfers::Pending->value,
                'AdjustedBy' => $validated['AdjustedBy'],
                'ModifiedBy' => auth()->id(),
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
                        'ModifiedBy' => auth()->id(),
                        'ModifiedOn' => now(),
                    ]);
                } else {
                    StockAdjustmentItem::create([
                        'AdjustmentId' => $adjustment->Id,
                        'Item' => $itemData['Item'],
                        'Reason' => $itemData['Reason'],
                        'AdjustmentQty' => $itemData['AdjustmentQty'],
                        'Remarks' => $itemData['Remarks'] ?? null,
                        'CreatedBy' => auth()->id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => auth()->id(),
                        'ModifiedOn' => now(),
                    ]);
                }
            }
        });
    }

   public function approve(int $adjustmentId)
{
    DB::transaction(function () use ($adjustmentId) {
        $adjustment = StockAdjustment::with('items')->findOrFail($adjustmentId);

        foreach ($adjustment->items as $item) {
            $stockItem = StockItem::firstOrNew([
                'ItemID' => $item->Item,
                'Branch' => $adjustment->Branch,
            ]);
            $stockItem->CurrentQty = ($stockItem->CurrentQty ?? 0) + $item->AdjustmentQty;
            $stockItem->save();

            $reasonDesc = CodeDetail::where('ID', $adjustment->Reason)->value('Description');
            if (strtolower($reasonDesc) === 'stock found') {
                continue;
            }

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
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        }

        $adjustment->update([
            'Status' => Transfers::Approved->value,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
            ->orderByDesc('id')
            ->value('SKUID');

        $nextNumber = $latestSKU
            ? ((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1
            : 1;

        $skuId = 'SKU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $adjustmentTypeId = CodeDetail::where('CodeID', 'Source')
            ->where('Description', 'Stock Adjustment')
            ->value('ID');

        foreach ($adjustment->items as $item) {
            $qty = $item->AdjustmentQty;
            $quantityIn = $qty > 0 ? $qty : 0;
            $quantityOut = $qty < 0 ? abs($qty) : 0;

            $lastToQty = StockTransaction::where('ItemID', $item->Item)
                    ->where('BranchID', $adjustment->Branch)
                    ->orderByDesc('TransactionDate')
                    ->orderByDesc('id')
                    ->value('BalanceQty');

                if ($lastToQty === null) {
                    $lastToQty = StockItem::where('ItemID', $item->Item)
                        ->where('Branch', $adjustment->Branch)
                        ->value('CurrentQty') ?? 0;
                }

            $newQty = ($lastToQty ?? 0) + $quantityIn - $quantityOut;

          $adjustmentQty = $quantityIn > 0 ? $quantityIn : $quantityOut;
            $totalCost = ($item->UnitCost ?? 0) * $adjustmentQty;

            // If it’s a stock-out, make total cost negative
            if ($quantityOut > 0) {
                $totalCost *= -1;
            }

            StockTransaction::create([
                'SKUID' => $skuId,
                'TransactionType' => $adjustmentTypeId,
                'ReferenceID' => $adjustment->Id,
                'ItemID' => $item->Item,
                'StoreID' => $item->StoreID ?? null,
                'BranchID' => $adjustment->Branch,
                'UnitCost' => $item->UnitCost ?? null,
                'UOMID' => $item->UOM,
                'QuantityIn' => $quantityIn,
                'QuantityOut' => $quantityOut,
                'BalanceQty' => $newQty,
                'TotalCost' => $totalCost,
                'TransactionDate' => now(),
                'Remarks' => 'Stock Adjustment within Branch ID ' . ($adjustment->Branch ?? 'Unknown'),
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedBy' => auth()->id(),
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

        Workflow::create([
            'Source' => 'StockAdjustment',
            'SourceID' => $adjustment->Id,
            'Stage' => Transfers::Approved->label(),
            'Status' => Transfers::Approved->value,
            'Notes' => 'Stock Adjustment approved',
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::where('Source', 'StockAdjustment')
            ->where('SourceID', $adjustment->Id)
            ->update(['Stage' => Transfers::Approved->label()]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($adjustment)
            ->event('approved')
            ->log("Stock Adjustment {$adjustment->AdjustmentId} was approved.");
    });
}

    public function reject(int $adjustmentId)
    {
        DB::transaction(function () use ($adjustmentId) {
            $adjustment = StockAdjustment::findOrFail($adjustmentId);

            $adjustment->update([
                'Status' => Transfers::Rejected->value,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);

            Workflow::create([
                'Source' => 'StockAdjustment',
                'SourceID' => $adjustment->Id,
                'Stage' => Transfers::Rejected->label(),
                'Status' => Transfers::Rejected->value,
                'Notes' => 'Stock Adjustment rejected',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);

            PendingWorkflow::where('Source', 'StockAdjustment')
                ->where('SourceID', $adjustment->Id)
                ->update(['Stage' => Transfers::Rejected->label()]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($adjustment)
                ->event('rejected')
                ->log("Stock Adjustment {$adjustment->AdjustmentId} was rejected.");
        });
    }
}
