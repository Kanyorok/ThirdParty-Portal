<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class StorePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::StoreView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::StoreView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::StoreCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::StoreUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::StoreDelete->value);
    }

    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::StoreUpdate->value);
    }
}
