<?php

namespace App\Policies\FleetManagement;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Fleet\FleetTripLog;

class FleetTripLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::FleetTripLogView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::FleetTripLogView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {

        return $user->can(PermissionEnum::FleetTripLogCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::FleetTripLogUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::FleetTripLogDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user): bool
    {
        return $user->can(PermissionEnum::FleetTripLogUpdate->value);
    }

    public function approve(User $user, FleetTripLog $requisition): bool
    {
        // Don't allow approving if already approved or rejected
        if (
            $requisition->Status === ApprovalEnum::Approved->value ||
            $requisition->Status === ApprovalEnum::Rejected->value
        ) {
            return false;
        }

        // Don't allow approving your own requisition


        // Must have the approval permission
        return $user->can(PermissionEnum::FleetTripLogApprove->value);
    }

    public function reject(User $user, FleetTripLog $requisition): bool
    {
        // Don't allow rejecting if already approved or rejected
        if (
            $requisition->Status === ApprovalEnum::Approved->value ||
            $requisition->Status === ApprovalEnum::Rejected->value
        ) {
            return false;
        }

        // Don't allow rejecting your own requisition


        // Must have the rejection permission
        return $user->can(PermissionEnum::FleetTripLogReject->value);
    }
}
