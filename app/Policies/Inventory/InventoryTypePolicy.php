<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Inventory\InventoryType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InventoryTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryTypeView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InventoryType $inventoryType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, InventoryType $inventoryType): bool
    {
        return $user->can(PermissionEnum::InventoryTypeCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InventoryType $inventoryType): bool
    {
        return $user->can(PermissionEnum::InventoryTypeUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user, InventoryType $inventoryType): bool
    {
        return $user->can(PermissionEnum::InventoryTypeDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, InventoryType $inventoryType): bool
    {
        return $user->can(PermissionEnum::InventoryTypeRestore->value);
    }

}
