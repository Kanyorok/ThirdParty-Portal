<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class PriceManagementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementDelete->value);
    }

    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementUpdate->value);
    }
}
