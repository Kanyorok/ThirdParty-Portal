<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\LoadOpeningStock;

class OpenStockPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {   
    }
    
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::OpeningStockView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::OpeningStockCreate->value);
    }

    public function view(User $user, LoadOpeningStock $loadOpeningStock): bool
    {
        return $user->can(PermissionEnum::PropertyRegistryView->value);
    }

    public function update(User $user, LoadOpeningStock $loadOpeningStock): bool
    {
        return $user->can(PermissionEnum::OpeningStockUpdate->value);
    }

    public function destroy(User $user, LoadOpeningStock $loadOpeningStock): bool
    {
        return $user->can(PermissionEnum::OpeningStockDelete->value);
    }
}
