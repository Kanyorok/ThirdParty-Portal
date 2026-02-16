<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\InsuranceProvider;

class InsuranceProviderPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InsuranceProviderView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::InsuranceProviderCreate->value);
    }

    public function view(User $user, InsuranceProvider $insuranceProvider): bool
    {
        return $user->can(PermissionEnum::InsuranceProviderView->value);
    }

    public function update(User $user, InsuranceProvider $insuranceProvider): bool
    {
        return $user->can(PermissionEnum::InsuranceProviderUpdate->value);
    }

    public function destroy(User $user, InsuranceProvider $insuranceProvider): bool
    {
        return $user->can(PermissionEnum::InsuranceProviderDelete->value);
    }
}
