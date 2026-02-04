<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;


class ItemMasterListPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MasterListView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::MasterListView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::MasterListCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::MasterListUpdate->value);
    }

    public function delete(User $user): bool
    {
        return $user->can(PermissionEnum::MasterListDelete->value);
    }

}
