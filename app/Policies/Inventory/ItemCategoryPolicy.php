<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\ItemCategories;

class ItemCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ItemCategoryView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ItemCategories $Category): bool
    {
        return $user->can(PermissionEnum::ItemCategoryView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::ItemCategoryCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ItemCategories $Category): bool
    {
        return $user->can(PermissionEnum::ItemCategoryUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user, ItemCategories $Category): bool
    {
        return $user->can(PermissionEnum::ItemCategoryDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, ItemCategories $Category): bool
    {
        return $user->can(PermissionEnum::ItemCategoryRestore->value);
    }
}
