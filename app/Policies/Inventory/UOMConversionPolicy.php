<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\FleetManagement\UOMConversion;

class UOMConversionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, UOMConversion $uomConversion): bool
    {
        return $user->can(PermissionEnum::UOMConversionUpdate->value);
    }
}
