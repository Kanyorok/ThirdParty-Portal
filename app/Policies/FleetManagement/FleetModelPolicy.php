<?php

namespace App\Policies\FleetManagement;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\FleetManagement\FleetModel;
use Illuminate\Auth\Access\Response;

class FleetModelPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::FleetModelView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::FleetModelView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::FleetModelCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::FleetModelUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::FleetModelDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, FleetModel $fleetModel): bool
    {
        return $user->can(PermissionEnum::FleetModelUpdate->value);
    }

}



