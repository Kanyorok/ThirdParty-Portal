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
        return $user->can(PermissionEnum::TenantMaintenanceView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::TenantMaintenanceCreate->value);
    }

    public function view(User $user, PropertyNewTenant $propertyNewTenant): bool
    {
        return $user->can(PermissionEnum::TenantMaintenanceView->value);
    }

    public function update(User $user, PropertyNewTenant $propertyNewTenant): bool
    {
        return $user->can(PermissionEnum::TenantClearanceUpdate->value);
    }

    public function destroy(User $user, PropertyNewTenant $propertyNewTenant): bool
    {
        return $user->can(PermissionEnum::TenantClearanceDelete->value);
    }
}
