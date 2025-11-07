<?php

namespace App\Policies\DMS;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\DMS\LegalHold;

class LegalHoldPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::DMSLegalHoldView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LegalHold $dMSLegalHold): bool
    {
        return $user->can(PermissionEnum::DMSLegalHoldView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::DMSLegalHoldCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LegalHold $dMSLegalHold): bool
    {
        return ($user->Id === $dMSLegalHold->CreatedBy || $user->can(PermissionEnum::DMSLegalHoldRelease->value));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LegalHold $dMSLegalHold): bool
    {
        return $this->update($user, $dMSLegalHold);
    }

    public function release(User $user, LegalHold $dMSLegalHold): bool
    {
        return $this->delete($user, $dMSLegalHold);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LegalHold $dMSLegalHold): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LegalHold $dMSLegalHold): bool
    {
        return false;
    }
}
