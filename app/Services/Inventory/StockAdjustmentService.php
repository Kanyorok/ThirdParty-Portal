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

class StockAdjustmentService
{
    public function create(array $validated, $user)
    {
        DB::transaction(function () use ($validated, $user) {
            $adjustment = StockAdjustment::create([
                'AdjustmentDate' => $validated['AdjustmentDate'],
                'Branch' => $validated['Branch'],
                'Reason' => $validated['Reason'],
                'AdjustedBy' => $validated['AdjustedBy'],
                'Status' => Transfers::Pending->value,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);

            $adjustment->AdjustmentId = 'SA/' . now()->format('Ymd') . '/' . str_pad($adjustment->Id, 4, '0', STR_PAD_LEFT);
            $adjustment->save();

            $sourceCodeId = CodeDetail::where('CodeID', 'Source')->where('Description', 'Stock Adjustment')->value('ID');

            foreach ($validated['items'] as $item) {
                $adjustmentItem = StockAdjustmentItem::create([
                    'AdjustmentId' => $adjustment->Id,
                    'Item' => $item['Item'],
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
        Log::info('StockAdjustmentService@update: Updating Stock Adjustment ID:', ['id' => $adjustment->Id, 'validated' => $validated]);

        DB::transaction(function () use ($adjustment, $validated) {
            $adjustment->fill([
                'AdjustmentDate' => $validated['AdjustmentDate'],
                'Reason' => $validated['Reason'],
                'Branch' => $validated['Branch'],
                'Status' => Transfers::Pending->value,
                'AdjustedBy' => $validated['AdjustedBy'],
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ])->save();


            $existingItemIds = $adjustment->items()->pluck('Item')->toArray();
            $incomingItemData = collect($validated['items']);
            $incomingItemIds = $incomingItemData->pluck('Item')->toArray();

            $itemsToDelete = array_diff($existingItemIds, $incomingItemIds);
            if (!empty($itemsToDelete)) {
                $adjustment->items()->whereIn('Item', $itemsToDelete)->delete();
                Log::info("StockAdjustmentService@update: Soft deleted items for Adjustment ID {$adjustment->Id}: " . implode(', ', $itemsToDelete));
            }

            foreach ($incomingItemData as $itemData) {
                $item = $adjustment->items()->where('Item', $itemData['Item'])->first();

                if ($item) {
                    $item->update([
                        'AdjustmentQty' => $itemData['AdjustmentQty'],
                        'Remarks' => $itemData['Remarks'] ?? null,
                        'ModifiedBy' => auth()->id(),
                        'ModifiedOn' => now(),
                    ]);
                    Log::info("StockAdjustmentService@update: Updated item {$itemData['Item']} for Adjustment ID {$adjustment->Id}.");
                } else {
                    StockAdjustmentItem::create([
                        'AdjustmentId' => $adjustment->Id,
                        'Item' => $itemData['Item'],
                        'AdjustmentQty' => $itemData['AdjustmentQty'],
                        'Remarks' => $itemData['Remarks'] ?? null,
                        'CreatedBy' => auth()->id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => auth()->id(),
                        'ModifiedOn' => now(),
                    ]);
                    Log::info("StockAdjustmentService@update: Created new item {$itemData['Item']} for Adjustment ID {$adjustment->Id}.");
                }
            }
        });


    }

    public function approve(int $adjustmentId)
    {
        DB::transaction(function () use ($adjustmentId) {
            $adjustment = StockAdjustment::with('items')->findOrFail($adjustmentId);

            foreach ($adjustment->items as $item) {
                // Update stock quantity
                $stockItem = StockItem::firstOrNew([
                    'ItemID' => $item->Item,
                    'Branch' => $adjustment->Branch,
                ]);
                $stockItem->CurrentQty = ($stockItem->CurrentQty ?? 0) + $item->AdjustmentQty;
                $stockItem->save();

                // Skip if reason is "Stock Found"
                $reasonDesc = CodeDetail::where('ID', $adjustment->Reason)->value('Description');
                if (strtolower($reasonDesc) === 'stock found') {
                    Log::info("Skipping InventoryHold creation for Item {$item->Item} due to Stock Found reason.");
                    continue;
                }

                $sourceCodeId = CodeDetail::where('CodeID', 'Source')
                    ->where('Description', 'Stock Adjustment')
                    ->value('ID');

                InventoryHold::create([
                    'ItemID' => $item->Item,
                    'BranchID' => $adjustment->Branch,
                    'Quantity' => abs($item->AdjustmentQty),
                    'Reason' => $adjustment->Reason,
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
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            Workflow::create([
                'Source' => 'StockAdjustment',
                'SourceID' => $adjustment->Id,
                'Stage' => Transfers::Approved->label(),
                'Status' => Transfers::Approved->value,
                'Notes' => 'Stock Adjustment approved',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            PendingWorkflow::where('Source', 'StockAdjustment')
                ->where('SourceID', $adjustment->Id)
                ->update(['Stage' => Transfers::Approved->label()]);

            activity()
                ->causedBy(Auth::user())
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
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            Workflow::create([
                'Source' => 'StockAdjustment',
                'SourceID' => $adjustment->Id,
                'Stage' => Transfers::Rejected->label(),
                'Status' => Transfers::Rejected->value,
                'Notes' => 'Stock Adjustment rejected',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            PendingWorkflow::where('Source', 'StockAdjustment')
                ->where('SourceID', $adjustment->Id)
                ->update(['Stage' => Transfers::Rejected->label()]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($adjustment)
                ->event('rejected')
                ->log("Stock Adjustment {$adjustment->AdjustmentId} was rejected.");
        });
    }
}
