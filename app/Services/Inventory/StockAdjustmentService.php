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
    

            foreach ($validated['items'] as $item) {
                StockAdjustmentItem::create([
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
    Log::info('Updating Stock Adjustment:', [
        'AdjustmentId' => $adjustment->Id,
        'Validated Data' => $validated 
    ]);

    DB::transaction(function () use ($adjustment, $validated) { 
    $adjustment->fill([
        'AdjustmentDate' => $validated['AdjustmentDate'],
        'Reason' => $validated['Reason'],
        'Branch' => $validated['Branch'],
        'Status' => Transfers::Pending->value, 
        'AdjustedBy' => $validated['AdjustedBy'],
        'CreatedBy' => auth()->id(),
        'ModifiedBy' => auth()->id(),
        'CreatedOn' => now(),
        'ModifiedOn' => now(),
    ])->save(); 

    foreach ($validated['items'] as $itemData) { 
        $item = StockAdjustmentItem::where('AdjustmentId', $adjustment->Id)
            ->where('Item', $itemData['Item'])
            ->first();

        if ($item) {
            $item->update([
                'AdjustmentQty' => $itemData['AdjustmentQty'],
                'Remarks' => $itemData['Remarks'] ?? null,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        } else {
            StockAdjustmentItem::create([
                'AdjustmentId' => $adjustment->Id,
                'Item' => $itemData['Item'],
                'AdjustmentQty' => $itemData['AdjustmentQty'],
                'Remarks' => $itemData['Remarks'] ?? null,
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);
        }
    }
});


    return redirect()->route('transactionsadjustment.index')->with('success', 'Stock adjustment updated successfully.');
}

    public function approve(int $adjustmentId)
    {
        DB::transaction(function () use ($adjustmentId) {
            $adjustment = StockAdjustment::with('items')->findOrFail($adjustmentId);
            
            foreach ($adjustment->items as $item) {
                $stockItem = StockItem::firstOrNew([
                    'ItemID' => $item->Item,
                    'Branch' => $adjustment->Branch
                ]);
                $stockItem->CurrentQty = ($stockItem->CurrentQty ?? 0) + $item->AdjustmentQty;
                $stockItem->save();
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($stockItem)
                    ->event('stock_adjusted')
                    ->log("Stock adjusted by {$item->AdjustmentQty} for Item {$item->Item} in Branch {$adjustment->Branch}");
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
