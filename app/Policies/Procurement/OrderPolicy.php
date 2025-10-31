<?php

namespace App\Policies\Procurement;

use App\Models\Auth\User;
use App\Models\Procurement\Order;
use App\Enums\Core\PermissionEnum;

class OrderPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderRead->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderWrite->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderDelete->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderUpdate->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderDelete->value);
    }

    /**
     * Approve a Purchase Order.
     */
    public function approve(User $user, Order $order): bool
    {
        return $user->can(PermissionEnum::PurchaseOrderApproval->value);
    }
}
