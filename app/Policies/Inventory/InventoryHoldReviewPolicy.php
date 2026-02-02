<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class InventoryHoldReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryHoldReviewView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryHoldReviewView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryHoldReviewCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    // public function update(User $user): bool
    // {
    //     return $user->can(PermissionEnum::InventoryHoldReviewUpdate->value);
    // }

    /**
     * Determine whether the user can delete the model.
     */
    // public function destroy(User $user): bool
    // {
    //     return $user->can(PermissionEnum::InventoryHoldReviewDestroy->value);
    // }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryHoldReviewUpdate->value);
    }
}
