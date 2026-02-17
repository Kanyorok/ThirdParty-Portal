<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class TransactionReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionReceiptView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionReceiptView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionReceiptCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionReceiptUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionReceiptDelete->value);
    }

    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionReceiptUpdate->value);
    }
}
