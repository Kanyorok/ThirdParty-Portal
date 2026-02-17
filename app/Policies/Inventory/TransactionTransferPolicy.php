<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class TransactionTransferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferCreate->value);
    }

    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferUpdate->value);
    }

    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferDelete->value);
    }

    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferUpdate->value);
    }

    public function approve(User $user): bool
    {

        return $user->can(PermissionEnum::TransactionTransferApproval->value);
    }
}
