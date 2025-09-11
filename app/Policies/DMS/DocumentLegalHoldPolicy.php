<?php

namespace App\Policies\DMS;

use App\Models\Auth\User;
use App\Models\DMS\DocumentLegalHold;

class DocumentLegalHoldPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DocumentLegalHold $documentLegalHold): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DocumentLegalHold $documentLegalHold): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DocumentLegalHold $documentLegalHold): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DocumentLegalHold $documentLegalHold): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DocumentLegalHold $documentLegalHold): bool
    {
        return false;
    }
}
