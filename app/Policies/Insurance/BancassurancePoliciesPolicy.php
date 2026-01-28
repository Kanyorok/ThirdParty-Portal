<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class BancassurancePoliciesPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BancassurancePolicyView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::BancassurancePolicyCreate->value);
    }

    public function view(User $user, BancassurancePoliciesPolicy $bancassurancepolicy): bool
    {
        return $user->can(PermissionEnum::BancassurancePolicyView->value);
    }

    public function update(User $user, BancassurancePoliciesPolicy $bancassurancepolicy): bool
    {
        return $user->can(PermissionEnum::BancassurancePolicyUpdate->value);
    }

    public function destroy(User $user, BancassurancePoliciesPolicy $bancassurancepolicy): bool
    {
        return $user->can(PermissionEnum::BancassurancePolicyDelete->value);
    }
}
