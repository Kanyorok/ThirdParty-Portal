<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryHold;
use App\Models\Inventory\InventoryHoldReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\Inventory\Transfers;

class InventoryHoldReviewService
{
    public function review(array $data): void
    {
        $id = $data['Id'] ?? $data['InventoryHoldID'] ?? null;
        if (!$id) {
            throw new \InvalidArgumentException('InventoryHold Id is required.');
        }

        $hold = InventoryHold::findOrFail($id);

        Log::info('Reviewing Inventory Hold', ['id' => $hold->Id]);

        $hold->update([
            'Condition' => $data['Condition'],
            'Notes' => $data['Notes'],
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);
    }

    public function dispose(int $id, array $extras = []): void
    {
        DB::transaction(function () use ($id, $extras) {
            $hold = InventoryHold::with('item')->findOrFail($id);

            if (!$hold->Reason) {
                Log::error("Disposal blocked: Missing reason for InventoryHold ID {$id}");
                throw new \Exception("Cannot dispose item without a defect reason.");
            }

            $review = InventoryHoldReview::create([
                'InventoryHoldID' => $hold->Id,
                'ItemID' => $hold->ItemID,
                'FromBranch' => $hold->BranchID,
                'Store' => $hold->Store,
                'Quantity' => $hold->Quantity,
                'Defect' => $hold->Reason,
                'Condition' => $extras['Condition'] ?? null,
                'Notes' => $extras['Notes'] ?? null,
                'Status' => Transfers::Disposed->value,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            Log::info("Disposal record created", ['review_id' => $review->Id]);

            $hold->update([
                'Status' => Transfers::Disposed->value,
                'DeletedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            $hold->delete();

            Log::info("InventoryHold marked as disposed and soft-deleted", ['id' => $hold->Id]);
        });
    }


    //**  public function markUnderRepair(int $id): void
    /*
        {
            $hold = InventoryHold::findOrFail($id);
            $hold->update([
                'Status'     => \App\Enums\Inventory\Transfers::UnderRepair->value,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            Log::info("Marked under repair", ['id' => $id]);
        }
        */

    public function returnToSender(int $id): void
    {
        $hold = InventoryHold::findOrFail($id);

        $from = $hold->FromBranch;
        $to = $hold->BranchID;

        $hold->update([
            'FromBranch' => $to,
            'BranchID' => $from,
            'Status' => \App\Enums\Inventory\Transfers::InTransit->value,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        Log::info("Returned to sender", ['id' => $id, 'from' => $from, 'to' => $to]);
    }


}
