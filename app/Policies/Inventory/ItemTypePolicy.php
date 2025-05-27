<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Inventory\ItemType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ItemTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ItemTypeView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ItemType $itemType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, ItemType $itemType): bool
    {
        return $user->can(PermissionEnum::ItemTypeCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ItemType $itemType): bool
    {
        return $user->can(PermissionEnum::ItemTypeUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user, ItemType $itemType): bool
    {
        return $user->can(PermissionEnum::ItemTypeDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, ItemType $itemType): bool
    {
        return $user->can(PermissionEnum::ItemTypeRestore->value);
    }

}
