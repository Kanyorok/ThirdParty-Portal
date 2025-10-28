<?php

namespace App\Policies\FleetManagement;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\Fleet\FleetRepairLog;
use Illuminate\Auth\Access\Response;

class FleetRepairLogPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::FleetRepairLogView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::FleetRepairLogView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user) : bool
    {
        
        return $user->can(PermissionEnum::FleetRepairLogCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::FleetRepairLogUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::FleetRepairLogDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::FleetRepairLogUpdate->value);
    }

}



