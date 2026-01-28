<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\ThirdParies\Board;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::Members->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        if (! $user->can(PermissionEnum::Managers->value) && (User::query()->where('ClientID', $client->ClientID)->exists() || Board::query()->where('ClientID', $client->ClientID)->exists())) {
            return false;
        }

        return $user->can(PermissionEnum::Members->value);
    }

    public function summary(User $user, Client $client): bool
    {
        return $user->can(PermissionEnum::Members->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::Members->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        return $user->can(PermissionEnum::Members->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->can(PermissionEnum::Members->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Client $client): bool
    {
        return $user->can(PermissionEnum::Members->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return $user->can(PermissionEnum::Members->value);
    }
}
