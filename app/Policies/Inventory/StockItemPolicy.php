<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Inventory\StockItem;
use App\Models\Auth\User;
use Illuminate\Auth\Access\Response;

class StockItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::StockItemView->value);
    }

    public function view(User $user, StockItem $stockItem): bool
    {
        return $user->can(PermissionEnum::StockItemView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::StockItemCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StockItem $stockItem): bool
    {
        return $user->can(PermissionEnum::StockItemUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::StockItemDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, StockItem $stockItem): bool
    {
        return false;
    }
}
