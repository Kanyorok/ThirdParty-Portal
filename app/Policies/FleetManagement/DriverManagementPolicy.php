<?php

namespace App\Policies\FleetManagement;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\FleetManagement\DriverManagement;
use Illuminate\Auth\Access\Response;

class DriverManagementPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::DriverManagementView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::DriverManagementView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::DriverManagementCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::DriverManagementUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::DriverManagementDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, DriverManagement $driverManagement): bool
    {
        return $user->can(PermissionEnum::DriverManagementUpdate->value);
    }

}



