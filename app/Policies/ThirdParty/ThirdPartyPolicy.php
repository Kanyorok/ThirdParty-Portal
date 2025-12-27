<?php

namespace App\Policies\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;

class ThirdPartyPolicy
{
    public function viewAny(ThirdPartyUser $user): bool
    {
        return $user->isActive();
    }

    public function view(ThirdPartyUser $user, ThirdParties $thirdParty): bool
    {
        return $user->ThirdPartyId === $thirdParty->Id;
    }

    public function create(ThirdPartyUser $user): bool
    {
        return $user->isActive() && $user->hasVerifiedEmail();
    }

    public function createSupplierProfile(ThirdPartyUser $user): bool
    {
        if (!$this->create($user)) {
            return false;
        }

        // Check if user already has a supplier profile
        if ($user->hasProfile() && $user->isSupplier()) {
            return false;
        }

        return true;
    }

    public function createTenantProfile(ThirdPartyUser $user): bool
    {
        if (!$this->create($user)) {
            return false;
        }

        // Check if user already has a tenant profile
        if ($user->hasProfile() && $user->isTenant()) {
            return false;
        }

        return true;
    }

    public function createCustomerProfile(ThirdPartyUser $user): bool
    {
        if (!$this->create($user)) {
            return false;
        }

        // Check if user already has a customer profile
        if ($user->hasProfile() && $user->isCustomer()) {
            return false;
        }

        return true;
    }

    public function update(ThirdPartyUser $user, ThirdParties $thirdParty): bool
    {
        return $user->ThirdPartyId === $thirdParty->Id && $user->isActive();
    }

    public function delete(ThirdPartyUser $user, ThirdParties $thirdParty): bool
    {
        // Only allow deletion if no active transactions
        return $user->ThirdPartyId === $thirdParty->Id
            && $user->isActive()
            && $this->canBeDeleted($thirdParty);
    }

    public function addProfileType(ThirdPartyUser $user, ThirdParties $thirdParty): bool
    {
        return $user->ThirdPartyId === $thirdParty->Id
            && $user->isActive()
            && $user->hasVerifiedEmail();
    }

    protected function canBeDeleted(ThirdParties $thirdParty): bool
    {
        // Add business logic to check if profile can be deleted
        // For example: no pending orders, invoices, contracts, etc.
        return true; // Placeholder
    }
}
