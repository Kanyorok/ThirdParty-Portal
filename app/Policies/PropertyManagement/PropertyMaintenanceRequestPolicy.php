<?php

namespace App\Policies\PropertyManagement;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;


class PropertyMaintenanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseScheduleView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseScheduleCreate->value);
    }

    public function view(User $user, PropertyMaintenanceRequest $PropertyMaintenanceRequest): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceRequestView->value);
    }

    public function update(User $user, PropertyMaintenanceRequest $PropertyMaintenanceRequest): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceRequestUpdate->value);
    }

    public function destroy(User $user, PropertyMaintenanceRequest $PropertyMaintenanceRequest): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceRequestDelete->value);
    }
}
