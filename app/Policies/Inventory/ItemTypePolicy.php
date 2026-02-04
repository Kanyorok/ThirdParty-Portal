<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class ItemTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ItemTypeView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::ItemTypeView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::ItemTypeCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::ItemTypeUpdate->value);
    }

    public function delete(User $user): bool
    {
        return $user->can(PermissionEnum::ItemTypeDelete->value);
    }

    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::ItemTypeUpdate->value);
    }
}
