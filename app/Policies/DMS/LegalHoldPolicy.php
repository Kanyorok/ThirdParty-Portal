<?php

namespace App\Policies\DMS;

use App\Models\Auth\User;
use App\Models\DMS\LegalHold;

class LegalHoldPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LegalHold $dMSLegalHold): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LegalHold $dMSLegalHold): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LegalHold $dMSLegalHold): bool
    {
        return true;
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
