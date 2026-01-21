<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\PlanLineItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanLineItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PlanManualInputRead->value) ||
            $user->can(PermissionEnum::PlanLineItemsRead->value);
    }

    public function view(User $user, PlanLineItem $lineItem): bool
    {
        return $user->can(PermissionEnum::PlanManualInputRead->value) ||
            $user->can(PermissionEnum::PlanLineItemsRead->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PlanManualInputWrite->value) ||
            $user->can(PermissionEnum::PlanLineItemsWrite->value);
    }

    public function store(User $user): bool
    {
        return $this->create($user);
    }

    public function update(User $user, PlanLineItem $lineItem): bool
    {
        return $user->can(PermissionEnum::PlanManualInputUpdate->value) ||
            $user->can(PermissionEnum::PlanLineItemsUpdate->value);
    }

    public function edit(User $user, PlanLineItem $lineItem): bool
    {
        return $this->update($user, $lineItem);
    }

    public function delete(User $user, PlanLineItem $lineItem): bool
    {
        return $user->can(PermissionEnum::PlanManualInputDelete->value) ||
            $user->can(PermissionEnum::PlanLineItemsDelete->value);
    }
}
