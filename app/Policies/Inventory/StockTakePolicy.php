<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;


class StockTakePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::StockTakeView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::StockTakeView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::StockTakeCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::StockTakeUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::StockTakeDelete->value);
    }
}
