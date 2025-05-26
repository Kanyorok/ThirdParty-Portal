<?php

namespace App\Policies\Procurement;

use App\Models\Auth\User;
use App\Models\Procurement\RFQ;
use Illuminate\Auth\Access\Response;

class RFQPolicy
{
    /**
     * Can view list of RFQs
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::RFQRead->value);
    }

    /**
     * Can view a single RFQ
     */
    public function view(User $user, RFQ $rfq): bool
    {
        // Only owner or users with permission
        return $this->isOwner($user, $rfq) || $user->can(PermissionEnum::RFQRead->value);
    }

    /**
     * Can create a new RFQ
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::RFQCreate->value);
    }

    /**
     * Can update an RFQ
     */
    public function update(User $user, RFQ $rfq): bool
    {
        // Only allow updates if the user is the owner and it's still in draft
        if ($this->isDraft($rfq)) {
            return $this->isOwner($user, $rfq);
        }

        return $user->can(PermissionEnum::RFQUpdate->value);
    }

    /**
     * Can delete an RFQ
     */
    public function delete(User $user, RFQ $rfq): bool
    {
        // Only the owner can delete if it's still draft
        return $this->isOwner($user, $rfq) && $this->isDraft($rfq);
    }

    /**
     * Internal helper — check if user owns the RFQ
     */
    protected function isOwner(User $user, RFQ $rfq): bool
    {
        return $user->Id === $rfq->CreatedBy;
    }

    /**
     * Internal helper — check if RFQ is in draft status
     */
    protected function isDraft(RFQ $rfq): bool
    {
        return $rfq->Status === 'Draft';
    }
}