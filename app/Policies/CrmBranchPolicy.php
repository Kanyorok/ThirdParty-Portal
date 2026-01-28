<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\Branch;

class CrmBranchPolicy
{
    public function before(User $user, string $ability): bool
    {
        // Legacy fallback or SuperAdmin check can go here if needed.
        // For now, we rely on individual checks.
        return false; // Don't block, but don't auto-grant everything based on legacy
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BranchView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Branch $crmBranch): bool
    {
        return $user->can(PermissionEnum::BranchView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::BranchCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Branch $crmBranch): bool
    {
        return $user->can(PermissionEnum::BranchUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Branch $crmBranch): bool
    {
        return $user->can(PermissionEnum::BranchDelete->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Branch $crmBranch): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Branch $crmBranch): bool
    {
        return false;
    }
}
