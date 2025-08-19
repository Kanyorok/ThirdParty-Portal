<?php

namespace App\Services\Inventory;

use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\ItemMasterList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class PriceManagementService
{
    public function create(array $data): PriceManagement
    {
        $pricing = new PriceManagement($data);
        $pricing->CreatedBy = Auth::id();
        $pricing->CreatedOn = Carbon::now();
        $pricing->ModifiedBy = Auth::id();
        $pricing->ModifiedOn = Carbon::now();

        $pricing->save();

        $pricing->PriceID = 'PR-' . str_pad($pricing->Id, 5, '0', STR_PAD_LEFT);
        $pricing->save();

        if ($item = ItemMasterList::find($pricing->ItemID)) {
            $item->ItemPrice = $pricing->Id;
            $item->save();
        }

        activity()
            ->performedOn($pricing)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data])
            ->log('Created Item Pricing');

        return $pricing;
    }

    public function update(PriceManagement $pricing, array $data): PriceManagement
    {
        $pricing->fill($data);
        $pricing->ModifiedBy = Auth::id();
        $pricing->ModifiedOn = Carbon::now();
        $pricing->save();

        if ($item = ItemMasterList::find($pricing->ItemID)) {
            if ($item->ItemPrice != $pricing->Id) {
                $item->ItemPrice = $pricing->Id;
                $item->save();
            }
        }

        activity()
            ->performedOn($pricing)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data])
            ->log('Updated Item Pricing');

        return $pricing;
    }

    public function delete(PriceManagement $pricing): bool
    {
        $pricing->DeletedBy = Auth::id();
        $pricing->save();
        $pricing->delete();

        if ($item = ItemMasterList::where('ItemPrice', $pricing->Id)->first()) {
            $item->ItemPrice = null;
            $item->save();
        }

        activity()
            ->performedOn($pricing)
            ->causedBy(Auth::user())
            ->log('Deleted Item Pricing');

        return true;
    }

    public function list($filters = [])
    {
        return PriceManagement::with(['item', 'uom'])
            ->orderBy('CreatedOn', 'asc')
            ->get();
    }
}
