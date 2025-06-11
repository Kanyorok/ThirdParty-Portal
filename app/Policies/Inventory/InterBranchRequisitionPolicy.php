<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Auth\User;
use Illuminate\Auth\Access\Response;

class InterBranchRequisitionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InterBranchRequisition $interBranchRequisition): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
     public function update(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, InterBranchRequisition $interBranchRequisition): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionDestroy->value);
    }

}
