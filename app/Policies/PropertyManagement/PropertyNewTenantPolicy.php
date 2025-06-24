<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyNewTenant;

class PropertyNewTenantPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::TenantMentenanceView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::TenantMentenanceCreate->value);
    }

    public function view(User $user, PropertyNewTenant $propertyNewTenant): bool
    {
        return $user->can(PermissionEnum::TenantMentenanceView->value);
    }

    public function update(User $user, PropertyNewTenant $propertyNewTenant): bool
    {
        return $user->can(PermissionEnum::TenantMentenanceUpdate->value);
    }

    public function destroy(User $user, PropertyNewTenant $propertyNewTenant): bool
    {
        return $user->can(PermissionEnum::TenantMentenanceDelete->value);
    }
}
