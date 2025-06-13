<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Inventory\InterBranchRequisition;
use App\Enums\Inventory\InterBranchRequisitionEnum;
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

    public function approve(User $user, InterBranchRequisition $requisition): bool
    {
        // Don't allow approving if already approved or rejected
        if (
            $requisition->Status === InterBranchRequisitionEnum::Approved->value ||
            $requisition->Status === InterBranchRequisitionEnum::Rejected->value
        ) {
            return false;
        }

        // Don't allow approving your own requisition
        // if ($requisition->CreatedBy === $user->Id) {
        //    return false;
        // }

        // Must have the approval permission
        return $user->can(PermissionEnum::InterBranchRequisitionApproval->value);
    }
}
