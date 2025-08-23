<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class BancassuranceClaimPolicy
{
    /**
     * Create a new policy instance.
     */
        public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceClaimView->value);
    }

    public function store(User $user, BancassuranceClaimPolicy $bancassuranceclaimpolicy): bool
    {
        return $user->can(PermissionEnum::BancassuranceClaimCreate->value);
    }

    public function view(User $user, BancassuranceClaimPolicy $bancassuranceclaimpolicy ): bool
    {
        return $user->can(PermissionEnum::BancassuranceClaimView->value);
    }

    public function update(User $user, BancassuranceClaimPolicy $bancassuranceclaimpolicy): bool
    {
        return $user->can(PermissionEnum::BancassuranceClaimUpdate->value);
    }

    public function destroy(User $user, BancassuranceClaimPolicy $bancassuranceclaimpolicy): bool
    {
        return $user->can(PermissionEnum::BancassuranceClaimDelete->value);
    }
}
