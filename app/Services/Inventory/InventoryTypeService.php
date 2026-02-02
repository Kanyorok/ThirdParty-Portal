<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InventoryTypeService
{
    public function create(array $data): InventoryType
    {
        $user = Auth::user();

        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = Carbon::now();
        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = Carbon::now();

        $type = InventoryType::create($data);

        activity()
            ->causedBy($user)
            ->performedOn($type)
            ->event('create')
            ->log('Created Inventory Type ' . $type->Id);

        return $type;
    }

    public function update(InventoryType $type, array $data): InventoryType
    {
        $user = Auth::user();

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = Carbon::now();

        $type->update($data);

        activity()
            ->causedBy($user)
            ->performedOn($type)
            ->event('update')
            ->log('Updated Inventory Type ' . $type->Id);

        return $type;
    }

    public function delete(InventoryType $type): void
    {
        $user = Auth::user();

        $type->DeletedBy = Auth::id();
        $type->save();
        $type->delete();

        activity()
            ->causedBy($user)
            ->performedOn($type)
            ->event('delete')
            ->log('Deleted Inventory Type ' . $type->Id);
    }
}
