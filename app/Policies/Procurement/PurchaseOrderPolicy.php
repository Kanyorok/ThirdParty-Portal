<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderRead->value);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderRead->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderWrite->value);
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderUpdate->value);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderDelete->value);
    }

    public function approve(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderApprove->value);
    }
}
