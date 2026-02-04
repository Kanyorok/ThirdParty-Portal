<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class InventoryHoldReviewPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryHoldReviewView->value);
    }


    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryHoldReviewView->value);
    }


    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::InventoryHoldReviewCreate->value);
    }

}
