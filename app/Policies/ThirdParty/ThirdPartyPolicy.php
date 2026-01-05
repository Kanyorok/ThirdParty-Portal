<?php

namespace App\Policies\ThirdParty;

use App\Models\Auth\User;
use App\Models\ThirdParty\ThirdParties;
use App\Enums\Core\PermissionEnum;
use Illuminate\Auth\Access\Response;

class ThirdPartyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ThirdPartyRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ThirdParties $thirdParty): bool
    {
        return $user->can(PermissionEnum::ThirdPartyRead->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::ThirdPartyCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ThirdParties $thirdParty): bool
    {
        return $user->can(PermissionEnum::ThirdPartyUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ThirdParties $thirdParty): bool
    {
        // Add specific logic here if needed (e.g. check if used in orders)
        return $user->can(PermissionEnum::ThirdPartyDelete->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ThirdParties $thirdParty): bool
    {
        return $user->can(PermissionEnum::ThirdPartyUpdate->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ThirdParties $thirdParty): bool
    {
        return $user->can(PermissionEnum::ThirdPartyDelete->value);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, ThirdParties $thirdParty): bool
    {
        return $user->can(PermissionEnum::ThirdPartyApprove->value);
    }
}
