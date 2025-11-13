<?php

namespace App\Policies\Procurement;

use App\Models\Auth\User;
use App\Models\Procurement\PlanLineItem;
use App\Enums\Core\PermissionEnum;

class PlanManualInputPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PlanManualInputRead->value);
    }


    public function view(User $user, PlanLineItem $lineItem): bool
    {
        return $user->can(PermissionEnum::PlanManualInputRead->value);
    }


    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PlanManualInputWrite->value);
    }


    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PlanManualInputWrite->value);
    }


    public function edit(User $user, PlanLineItem $lineItem): bool
    {
        return $user->can(PermissionEnum::PlanManualInputUpdate->value);
    }


    public function update(User $user, PlanLineItem $lineItem): bool
    {
        return $user->can(PermissionEnum::PlanManualInputUpdate->value);
    }


    public function delete(User $user, PlanLineItem $lineItem): bool
    {
        return $user->can(PermissionEnum::PlanManualInputDelete->value);
    }
}
