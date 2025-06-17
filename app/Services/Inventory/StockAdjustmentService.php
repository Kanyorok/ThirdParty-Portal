<?php

namespace App\Services\Inventory;

use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockAdjustmentItem;
use App\Models\Inventory\StockItem;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use App\Enums\Inventory\Transfers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

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
            

            foreach ($validated['items'] as $item) {
                $adjustment->items()->create([
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
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            PendingWorkflow::updateOrCreate(
                ['Source' => 'StockAdjustment', 'SourceID' => $adjustment->Id],
                [
                    'Stage' => Transfers::Pending->label(),
                    'UserId' => Auth::id(),
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]
            );
        });
    }

    public function approve(int $adjustmentId)
    {
        DB::transaction(function () use ($adjustmentId) {
            $adjustment = StockAdjustment::with('items')->findOrFail($adjustmentId);

            // Update stock quantities now (AFTER approval)
            foreach ($adjustment->items as $item) {
                $stockItem = StockItem::where('ItemID', $item->Item)
                    ->where('Branch', $adjustment->Branch)
                    ->first();

                $newQty = ($stockItem?->CurrentQty ?? 0) + $item->AdjustmentQty;

                if ($stockItem) {
                    $stockItem->CurrentQty = $newQty;
                    $stockItem->save();
                } else {
                    $stockItem = StockItem::create([
                        'ItemID' => $item->Item,
                        'Branch' => $adjustment->Branch,
                        'CurrentQty' => $newQty,
                    ]);
                }

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($stockItem)
                    ->event('stock_adjusted')
                    ->log("Stock adjusted by {$item->AdjustmentQty} for Item {$item->Item} in Branch {$adjustment->Branch}");
            }

            $adjustment->Status = Transfers::Approved->value;
            $adjustment->ModifiedBy = Auth::id();
            $adjustment->ModifiedOn = now();
            $adjustment->save();

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
                ->update(['Stage' => Transfers::Approved]);

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

            $adjustment->Status = Transfers::Rejected->value;
            $adjustment->ModifiedBy = Auth::id();
            $adjustment->ModifiedOn = now();
            $adjustment->save();

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
                ->update(['Stage' => Transfers::Rejected]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($adjustment)
                ->event('rejected')
                ->log("Stock Adjustment {$adjustment->AdjustmentId} was rejected.");
        });
    }
}
