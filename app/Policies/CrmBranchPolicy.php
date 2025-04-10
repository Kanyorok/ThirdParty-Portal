<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\CrmBranch;
use App\Models\User;

class CrmBranchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::Teams->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CrmBranch $crmBranch): bool
    {
        return $user->can(PermissionEnum::Teams->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::Teams->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CrmBranch $crmBranch): bool
    {
        return $user->can(PermissionEnum::Teams->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CrmBranch $crmBranch): bool
    {
        return $user->can(PermissionEnum::Teams->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CrmBranch $crmBranch): bool
    {
        return $user->can(PermissionEnum::Teams->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CrmBranch $crmBranch): bool
    {
        return $user->can(PermissionEnum::Teams->value);
    }
}
