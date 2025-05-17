<?php

namespace App\Policies\Procurement;

use App\Models\Auth\User;
use App\Models\Procurement\RequisitionLine;

class RequisitionLinesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
//        return $user->can(PermissionEnum::RequisitionItemsRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RequisitionLine $requisitionLines): bool
    {
//        return $user->can(PermissionEnum::RequisitionItemsRead->value);

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
//        return $user->can(PermissionEnum::RequisitionItemsWrite->value);
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RequisitionLine $requisitionLines): bool
    {
//        return $user->can(PermissionEnum::RequisitionItemsUpdate->value);
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RequisitionLine $requisitionLines): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RequisitionLine $requisitionLines): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RequisitionLine $requisitionLines): bool
    {
        return true;
    }
}
