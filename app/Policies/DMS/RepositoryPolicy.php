<?php

namespace App\Policies\DMS;

use App\Models\Auth\User;
use App\Models\DMS\Repository;

class RepositoryPolicy
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
    public function view(User $user, Repository $repository): bool
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
    public function update(User $user, Repository $repository): bool
    {
        return true; //todo if public creator or admin
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Repository $repository): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Repository $repository): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Repository $repository): bool
    {
        return false;
    }
}
