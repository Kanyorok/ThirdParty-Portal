<?php

namespace App\Policies\FleetManagement;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\FleetManagement\FleetMake;
use Illuminate\Auth\Access\Response;

class FleetMakePolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::FleetMakeView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::FleetMakeView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::FleetMakeCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::FleetMakeUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::FleetMakeDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::FleetMakeUpdate->value);
    }

}



