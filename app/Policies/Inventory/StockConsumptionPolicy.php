<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class StockConsumptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::StockConsumptionView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::StockConsumptionView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::StockConsumptionCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::StockConsumptionUpdate->value);
    }

    public function delete(User $user): bool
    {
        return $user->can(PermissionEnum::StockConsumptionDelete->value);
    }
    
}
