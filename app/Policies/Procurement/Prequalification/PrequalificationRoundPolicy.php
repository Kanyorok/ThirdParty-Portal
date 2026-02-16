<?php

namespace App\Policies\Procurement\Prequalification;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\Prequalification\PrequalificationRound;

class PrequalificationRoundPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PrequalificationRoundRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PrequalificationRound $prequalificationRound): bool
    {
        return $user->can(PermissionEnum::PrequalificationRoundRead->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PrequalificationRoundCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PrequalificationRound $prequalificationRound): bool
    {
        return $user->can(PermissionEnum::PrequalificationRoundUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PrequalificationRound $prequalificationRound): bool
    {
        return $user->can(PermissionEnum::PrequalificationRoundDelete->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PrequalificationRound $prequalificationRound): bool
    {
        return $user->can(PermissionEnum::PrequalificationRoundUpdate->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PrequalificationRound $prequalificationRound): bool
    {
        return $user->can(PermissionEnum::PrequalificationRoundDelete->value);
    }

    public function approve(User $user, PrequalificationRound $prequalificationRound): bool
    {
        return $user->can(PermissionEnum::PrequalificationRoundApproval->value);
    }
}
