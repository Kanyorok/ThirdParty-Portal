<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\StockItem;

class StockItemPolicy
{
   
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::StockItemView->value);
    }

    public function view(User $user, StockItem $stockItem): bool
    {
        return $user->can(PermissionEnum::StockItemView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::StockItemCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::StockItemUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::StockItemDelete->value);
    }

    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::StockItemUpdate->value);
    }
}
