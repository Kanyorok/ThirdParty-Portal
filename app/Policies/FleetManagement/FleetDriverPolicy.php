<?php

namespace App\Policies\FleetManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class FleetDriverPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::FleetDriverView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::FleetDriverView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {

        return $user->can(PermissionEnum::FleetDriverCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::FleetDriverUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::FleetDriverDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::FleetDriverUpdate->value);
    }
}
