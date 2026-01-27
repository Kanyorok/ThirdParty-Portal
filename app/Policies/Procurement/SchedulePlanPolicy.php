<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\SchedulePlan;

class SchedulePlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::SchedulePlanRead->value);
    }

    public function store(User $user, SchedulePlan $schedulePlan): bool
    {
        return $user->can(PermissionEnum::SchedulePlanWrite->value);
    }

    public function view(User $user, SchedulePlan $schedulePlan): bool
    {
        return $user->can(PermissionEnum::SchedulePlanRead->value);
    }

    public function update(User $user, SchedulePlan $schedulePlan): bool
    {
        return $user->can(PermissionEnum::SchedulePlanUpdate->value);
    }
}
