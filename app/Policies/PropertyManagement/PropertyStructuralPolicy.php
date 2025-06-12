<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class PropertyStructuralPolicy
{
    /**
     * Create a new policy instance.
     */

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyStructuralView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyStructuralCreate->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyStructuralView->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyStructuralUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyStructuralDelete->value);
    }
}
