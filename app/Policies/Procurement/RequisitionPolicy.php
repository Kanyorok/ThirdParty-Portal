<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Procurement\Requisitions;
use App\Models\User;

class RequisitionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
//        return $user->can(PermissionEnum::RequisitionRead->value);

        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Requisitions $requisition): bool
    {
//        return $user->can(PermissionEnum::RequisitionRead->value);

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
//        return $user->can(PermissionEnum::RequisitionWrite->value);
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Requisitions $requisition): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Requisitions $requisition): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Requisitions $requisition): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Requisitions $requisition): bool
    {
        return true;
    }
}
