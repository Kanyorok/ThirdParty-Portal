<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\UnitOfMeasure;

class UnitOfMeasurePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::UOMView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UnitOfMeasure $uom): bool
    {
        return $user->can(PermissionEnum::UOMView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::UOMCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::UOMUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::UOMDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, UnitOfMeasure $uom): bool
    {
        return $user->can(PermissionEnum::UOMRestore->value);
    }
}
