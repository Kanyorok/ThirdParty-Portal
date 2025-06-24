<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class PropertyTenantClearancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::TenantClearanceView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::TenantClearanceCreate->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::TenantClearanceView->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::TenantClearanceUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::TenantClearanceDelete->value);
    }
}
