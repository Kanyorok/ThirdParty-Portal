<?php

namespace App\Services\Inventory;

use App\Models\Inventory\ItemType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ItemTypeService
{
    public function create(array $data): ItemType
    {
        $itemType = ItemType::create([
            'TypeName' => $data['TypeName'],
            'StockTracked' => $data['StockTracked'],
            'RequiresTagging' => $data['RequiresTagging'],
            'Active' => $data['Active'] ?? 0,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($itemType)
            ->event('create')
            ->log('Created Item Type ' . $itemType->Id);

        return $itemType;
    }

    public function update(ItemType $itemType, array $data): ItemType
    {
        $itemType->update([
            'TypeName' => $data['TypeName'],
            'StockTracked' => $data['StockTracked'],
            'RequiresTagging' => $data['RequiresTagging'],
            'Active' => $data['Active'] ?? 0,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($itemType)
            ->event('update')
            ->log('Updated Item Type ' . $itemType->Id);

        return $itemType;
    }

    public function delete(ItemType $itemType): void
    {
        $itemType->DeletedBy = Auth::id();
        $itemType->save();
        $itemType->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($itemType)
            ->event('delete')
            ->log('Deleted Item Type ' . $itemType->Id);
    }
}
