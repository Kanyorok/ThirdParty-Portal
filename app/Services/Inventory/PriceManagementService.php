<?php

namespace App\Services\Inventory;

use App\Models\Inventory\PriceManagement;
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

        activity()
            ->performedOn($pricing)
            ->causedBy(Auth::user())
            ->log('Deleted Item Pricing');

        return true;
    }

    public function list($filters = [])
    {
        $query = PriceManagement::with(['item', 'uom']);
        // Add filter logic if needed.
        return $query->latest('CreatedOn')->get();
    }
}