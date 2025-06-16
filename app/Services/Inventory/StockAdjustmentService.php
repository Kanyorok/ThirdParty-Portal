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

            $adjustment->AdjustmentId = 'SA/' . now()->format('Ymd') . '/' . str_pad($adjustment->id, 4, '0', STR_PAD_LEFT);
            $adjustment->save();

            foreach ($validated['items'] as $item) {
                $stockItem = StockItem::where('ItemID', $item['Item'])
                    ->where('Branch', $validated['Branch'])
                    ->first();

                $oldQty = $stockItem ? $stockItem->CurrentQty : 0;
                $newQty = $oldQty + $item['AdjustmentQty'];

                if ($stockItem) {
                    $stockItem->CurrentQty = $newQty;
                    $stockItem->save();
                } else {
                    $stockItem = StockItem::create([
                        'ItemID' => $item['Item'],
                        'Branch' => $validated['Branch'],
                        'CurrentQty' => $newQty,
                    ]);
                }

                $adjustment->items()->create([
                    'Item' => $item['Item'],
                    'AdjustmentQty' => $item['AdjustmentQty'],
                    'Remarks' => $item['Remarks'] ?? null,
                    'CreatedBy' => auth()->id(),
    'CreatedOn' => now(),
    'ModifiedBy' => auth()->id(),
    'ModifiedOn' => now(),
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($stockItem)
                    ->event('stock_adjusted')
                    ->log("Stock adjusted by {$item['AdjustmentQty']} for Item {$item['Item']} in Branch {$validated['Branch']}");
            }

            // Register into Workflow
            $workflow = Workflow::create([
            'Source' => 'StockAdjustment',
            'SourceID' => $adjustment->Id,
            'Stage' => Transfers::Pending->label(),
            'Status' => Transfers::Pending->value,
            'Notes' => 'Adjustment submitted, awaiting approval',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
                
            ]);

                PendingWorkflow::updateOrCreate(
            [
                'Source' => 'StockAdjustment',
                'SourceID' => $adjustment->Id,
            ],
            [
                'Stage' => Transfers::Pending->label(),
                'UserId' => Auth::id(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => Carbon::now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now(),

            ]);
        });
    }
    public function approve(int $adjustmentId)
{
    DB::transaction(function () use ($adjustmentId) {
        $adjustment = StockAdjustment::with('items')->findOrFail($adjustmentId);

        // Update adjustment status
        $adjustment->Status = Transfers::Approved->value;
        $adjustment->ModifiedBy = Auth::id();
        $adjustment->ModifiedOn = now();
        $adjustment->save();

        // Log to Workflow
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

        // Remove from Pending Workflow
        PendingWorkflow::where('Source', 'StockAdjustment')
            ->where('SourceID', $adjustment->Id)
            ->delete();

        // Optional: Log activity
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
            ->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($adjustment)
            ->event('rejected')
            ->log("Stock Adjustment {$adjustment->AdjustmentId} was rejected.");
    });
}


}
