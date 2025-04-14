<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    public function meeting(User $user): bool
    {
        return $user->can(PermissionEnum::BoardMeeting->value);
    }


    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BoardManage->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Board $board): bool
    {
        return $user->can(PermissionEnum::BoardManage->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::BoardManage->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Board $board): bool
    {
        return $user->can(PermissionEnum::BoardManage->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Board $board): bool
    {
        return $user->can(PermissionEnum::BoardManage->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Board $board): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Board $board): bool
    {
        return false;
    }
}
