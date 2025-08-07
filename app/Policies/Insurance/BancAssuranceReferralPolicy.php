<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\BancAssuranceReferral;

class BancAssuranceReferralPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceReferralView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceReferralCreate->value);
    }

    public function view(User $user, BancAssuranceReferral $bancAssuranceReferral ): bool
    {
        return $user->can(PermissionEnum::BancassuranceReferralView->value);
    }

    public function update(User $user, BancAssuranceReferral $bancAssuranceReferral): bool
    {
        return $user->can(PermissionEnum::BancassuranceReferralUpdate->value);
    }

    public function destroy(User $user, BancAssuranceReferral $bancAssuranceReferral): bool
    {
        return $user->can(PermissionEnum::BancassuranceReferralDelete->value);
    }
}
