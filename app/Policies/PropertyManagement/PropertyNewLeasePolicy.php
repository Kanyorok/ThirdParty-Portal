<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class PropertyNewLeasePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyNewLeaseView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyNewLeaseView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyNewLeaseCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyNewLeaseUpdate->value);
    }

    public function delete(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyNewLeaseDelete->value);
    }

    public function approve(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyNewLeaseApproval->value);
    }

    public function reject(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyNewLeaseApproval->value);
    }
}
