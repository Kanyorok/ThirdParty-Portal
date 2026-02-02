<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Store;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StoreService
{
    public function create(array $data): Store
    {
        $store = new Store($data);
        $store->CreatedBy = Auth::id();
        $store->CreatedOn = Carbon::now();
        $store->ModifiedBy = Auth::id();
        $store->ModifiedOn = Carbon::now();
        $store->save();

        // Generate StoreID like 'STR-00001'
        $store->StoreID = 'STR-' . str_pad($store->Id, 5, '0', STR_PAD_LEFT);
        $store->save();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($store)
            ->withProperties(['attributes' => $store->getAttributes()])
            ->log('Created Store');

        return $store;
    }

    public function update(Store $store, array $data): Store
    {
        $original = $store->getOriginal();

        $store->update($data);
        $store->ModifiedBy = Auth::id();
        $store->ModifiedOn = Carbon::now();
        $store->save();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($store)
            ->withProperties([
                'old' => $original,
                'attributes' => $store->getAttributes(),
            ])
            ->log('Updated Store');

        return $store;
    }

    public function delete(Store $store): bool
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($store)
            ->withProperties(['attributes' => $store->getAttributes()])
            ->log('Deleted Store');

        return $store->delete();
    }
}
