<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Enums\Inventory\InterBranchRequisitionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\InterBranchRequisition;

class InterBranchRequisitionPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionView->value);
    }

    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionView->value);
    }


    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionCreate->value);
    }


    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionUpdate->value);
    }


    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionDelete->value);
    }


    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::InterBranchRequisitionUpdate->value);
    }

    public function approve(User $user, InterBranchRequisition $requisition): bool
    {
        if (
            $requisition->Status === InterBranchRequisitionEnum::Approved->value ||
            $requisition->Status === InterBranchRequisitionEnum::Rejected->value
        ) {
            return false;
        }


        return $user->can(PermissionEnum::InterBranchRequisitionApproval->value);
    }
}
