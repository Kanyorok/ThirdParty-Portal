<?php

namespace App\Policies\PropertyManagement;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\PropertyManagement\PropertyLeaseSchedule;

class PropertyLeaseSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseScheduleView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseScheduleCreate->value);
    }

    public function view(User $user, PropertyLeaseSchedule $PropertyLeaseSchedule): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseScheduleView->value);
    }

    public function update(User $user, PropertyLeaseSchedule $PropertyLeaseSchedule): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseScheduleUpdate->value);
    }

    public function destroy(User $user, PropertyLeaseSchedule $PropertyLeaseSchedule): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseScheduleDelete->value);
    }

    public function print(User $user, PropertyLeaseSchedule $PropertyLeaseSchedule): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseSchedulePrint->value);
    }
}
