<?php

namespace App\Policies\FleetManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class FleetInspectionSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::FleetInsuranceTrackerView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::FleetInsuranceTrackerView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {

        return $user->can(PermissionEnum::FleetInsuranceTrackerCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::FleetInsuranceTrackerUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::FleetInsuranceTrackerDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::FleetInsuranceTrackerUpdate->value);
    }
}
