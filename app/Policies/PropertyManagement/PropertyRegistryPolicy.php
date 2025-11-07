<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyRegistry;

class PropertyRegistryPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyRegistryView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyRegistryCreate->value);
    }

    public function view(User $user, PropertyRegistry $propertyRegistry): bool
    {
        return $user->can(PermissionEnum::PropertyRegistryView->value);
    }

    public function update(User $user, PropertyRegistry $propertyRegistry): bool
    {
        return $user->can(PermissionEnum::PropertyRegistryUpdate->value);
    }

    public function destroy(User $user, PropertyRegistry $propertyRegistry): bool
    {
        return $user->can(PermissionEnum::PropertyRegistryDelete->value);
    }
}
