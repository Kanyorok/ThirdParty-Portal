<?php

namespace App\Policies\PropertyManagement;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\PropertyManagement\PropertyMaintenanceWorkCompletion;

class PropertyMaintenanceWorkCompletionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceWorkCompletionView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceWorkCompletionCreate->value);
    }

    public function view(User $user, PropertyMaintenanceWorkCompletion $PropertyMaintenanceWorkCompletion): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceWorkCompletionView->value);
    }

    public function update(User $user, PropertyMaintenanceWorkCompletion $PropertyMaintenanceWorkCompletion): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceWorkCompletionUpdate->value);
    }

    public function destroy(User $user, PropertyMaintenanceWorkCompletion $PropertyMaintenanceWorkCompletion): bool
    {
        return $user->can(PermissionEnum::PropertyMaintenanceWorkCompletionDelete->value);
    }
}
