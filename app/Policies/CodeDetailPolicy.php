<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\CodeDetail;
use App\Models\User;

class CodeDetailPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ListsView->value);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::ListsView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::ListsUpdate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CodeDetail $model): bool
    {

        return $user->can(PermissionEnum::ListsUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CodeDetail $model): bool
    {
        return $user->can(PermissionEnum::ListsUpdate->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CodeDetail $model): bool
    {
        return $user->can(PermissionEnum::ListsUpdate->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CodeDetail $model): bool
    {
        return false;
    }
}
