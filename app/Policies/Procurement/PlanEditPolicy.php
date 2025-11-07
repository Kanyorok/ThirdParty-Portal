<?php

namespace App\Policies\Procurement;

use App\Models\Auth\User;
use App\Models\Procurement\PlanLineItems;
use App\Enums\Core\PermissionEnum;

class PlanEditPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PlanEditRead->value);
    }

    public function view(User $user, PlanLineItems $item): bool
    {
        return $user->can(PermissionEnum::PlanEditRead->value);
    }

    public function update(User $user, PlanLineItems $item): bool
    {
        return $user->can(PermissionEnum::PlanEditUpdate->value);
    }

    public function delete(User $user, PlanLineItems $item): bool
    {
        return $user->can(PermissionEnum::PlanEditDelete->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PlanEditWrite->value);
    }
}
