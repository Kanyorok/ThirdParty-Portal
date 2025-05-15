<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\ThirdParies\Competitor;

class CompetitorPolicy
{
    public function llm(User $user, Competitor $competitor): bool
    {
        if (is_array($competitor->Processing)) {
            return false;
        }
        return $user->can(PermissionEnum::CompetitorLLM->value);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::Competitor->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Competitor $competitor): bool
    {
        return $user->can(PermissionEnum::Competitor->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::Competitor->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Competitor $competitor): bool
    {
        if (is_array($competitor->Processing)) {
            return false;
        }
        return $user->can(PermissionEnum::Competitor->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Competitor $competitor): bool
    {
        if (is_array($competitor->Processing)) {
            return false;
        }

        return $user->can(PermissionEnum::Competitor->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Competitor $competitor): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Competitor $competitor): bool
    {
        return false;
    }
}
