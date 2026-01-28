<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\StockAdjustment;

class StockAdjustmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentRestore->value);
    }

    /**
     * Determine whether the user can approve the stock adjustment.
     */
    public function approve(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentApproval->value);
    }
}
