<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class StockAdjustmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentDelete->value);
    }

    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentUpdate->value);
    }

    public function approve(User $user): bool
    {
        return $user->can(PermissionEnum::StockAdjustmentApproval->value);
    }
}
