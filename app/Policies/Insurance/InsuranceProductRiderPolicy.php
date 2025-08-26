<?php

namespace App\Policies\Insurance;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\InsuranceProductRider;

class InsuranceProductRiderPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InsuranceProductRiderView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::InsuranceProductRiderCreate->value);
    }

    public function view(User $user, InsuranceProductRider $insuranceProductrider): bool
    {
        return $user->can(PermissionEnum::InsuranceProductRiderView->value);
    }

    public function update(User $user, InsuranceProductRider $insuranceProductrider): bool
    {
        return $user->can(PermissionEnum::InsuranceProductRiderUpdate->value);
    }

    public function destroy(User $user, InsuranceProductRider $insuranceProductrider): bool
    {
        return $user->can(PermissionEnum::InsuranceProductRiderDelete->value);
    }

}
