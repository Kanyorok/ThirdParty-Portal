<?php

namespace App\Policies\FleetManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class FleetServiceAlertPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::FleetServiceAlertView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function acknowledge(User $user): bool
    {
        return $user->can(PermissionEnum::FleetServiceAlertAcknowledge->value);
    }
}
