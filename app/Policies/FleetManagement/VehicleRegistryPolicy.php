<?php

namespace App\Policies\FleetManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\FleetManagement\VehicleRegistry;

class VehicleRegistryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::VehicleRegistryView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::VehicleRegistryView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::VehicleRegistryCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::VehicleRegistryUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::VehicleRegistryDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, VehicleRegistry $vehicleRegistry): bool
    {
        return $user->can(PermissionEnum::VehicleRegistryUpdate->value);
    }
}
