<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\BancassuranceClaimClosure;

class BancassuranceClaimClosurePolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InsuranceClaimClosureView->value);
    }

    public function store(User $user, BancassuranceClaimClosure $bancassuranceclaimclosure): bool
    {
        return $user->can(PermissionEnum::InsuranceClaimClosureCreate->value);
    }

    public function view(User $user,  BancassuranceClaimClosure $bancassuranceclaimclosure): bool
    {
        return $user->can(PermissionEnum::InsuranceClaimClosureView->value);
    }

    public function update(User $user,  BancassuranceClaimClosure $bancassuranceclaimclosure): bool
    {
        return $user->can(PermissionEnum::InsuranceClaimClosureUpdate->value);
    }

    public function destroy(User $user,  BancassuranceClaimClosure $bancassuranceclaimclosure): bool
    {
        return $user->can(PermissionEnum::InsuranceClaimClosureDelete->value);
    }
}
