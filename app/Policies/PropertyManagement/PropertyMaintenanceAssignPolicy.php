<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;

class PropertyMaintenanceAssignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceAssignView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceAssignCreate->value);
    }

    public function view(User $user, PropertyMaintenanceAssign $PropertyMaintenanceAssign): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceAssignView->value);
    }

    public function update(User $user, PropertyMaintenanceAssign $PropertyMaintenanceAssign): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceAssignUpdate->value);
    }

    public function destroy(User $user, PropertyMaintenanceAssign $PropertyMaintenanceAssign): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceAssignDelete->value);
    }
}
