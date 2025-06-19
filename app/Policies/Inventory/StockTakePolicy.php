<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\StockTake;

class StockTakePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::StockTakeView->value);
    }

    public function view(User $user, StockTake $stockTake): bool
    {
        return $user->can(PermissionEnum::StockTakeView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::StockTakeCreate->value);
    }

    public function update(User $user, StockTake $stockTake): bool
    {
        return $user->can(PermissionEnum::StockTakeUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::StockTakeDestroy->value);
    }

}
