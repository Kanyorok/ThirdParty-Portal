<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class PropertyLeaseTerminationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseTerminationView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseTerminationView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseTerminationCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseTerminationUpdate->value);
    }

    public function delete(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseTerminationDelete->value);
    }
}
