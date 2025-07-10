<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class PrequalificationPeriodPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PrequalificationPeriodRead->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PrequalificationPeriodWrite->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::PrequalificationPeriodRead->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::PrequalificationPeriodWrite->value);
    }

    public function delete(User $user): bool
    {
        return $user->can(PermissionEnum::PrequalificationPeriodDelete->value);
    }
}
