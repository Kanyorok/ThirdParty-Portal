<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class UOMConversionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionDelete->value);
    }

    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::UOMConversionUpdate->value);
    }
}
