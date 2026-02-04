<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\InventoryType;

class InventoryTypePolicy
{
    
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryTypeView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryTypeView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryTypeCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryTypeUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryTypeDelete->value);
    }


    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryTypeUpdate->value);
    }
}
