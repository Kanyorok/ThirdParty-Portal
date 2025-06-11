<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyType;

class PropertyTypePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyTypeView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyTypeCreate->value);
    }

    public function view(User $user, PropertyType $propertyType): bool
    {
        return $user->can(PermissionEnum::PropertyTypeView->value);
    }

    public function update(User $user, PropertyType $propertyType): bool
    {
        return $user->can(PermissionEnum::PropertyTypeUpdate->value);
    }

    public function destroy(User $user, PropertyType $propertyType): bool
    {
        return $user->can(PermissionEnum::PropertyTypeDelete->value);
    }
}
