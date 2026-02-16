<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\ItemCategories;

class ItemCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ItemCategoryView->value);
    }

    public function view(User $user, ItemCategories $Category): bool
    {
        return $user->can(PermissionEnum::ItemCategoryView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::ItemCategoryCreate->value);
    }

    public function update(User $user, ItemCategories $Category): bool
    {
        return $user->can(PermissionEnum::ItemCategoryUpdate->value);
    }

    public function destroy(User $user, ItemCategories $Category): bool
    {
        return $user->can(PermissionEnum::ItemCategoryDelete->value);
    }

    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::ItemCategoryUpdate->value);
    }
}
