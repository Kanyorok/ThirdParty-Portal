<?php

namespace App\Policies\Insurance;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\InsuranceProduct;

class InsuranceProductPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InsuranceProductView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::InsuranceProductCreate->value);
    }

    public function view(User $user, InsuranceProduct $insuranceProduct): bool
    {
        return $user->can(PermissionEnum::InsuranceProductView->value);
    }

    public function update(User $user, InsuranceProduct $insuranceProduct): bool
    {
        return $user->can(PermissionEnum::InsuranceProductUpdate->value);
    }

    public function destroy(User $user, InsuranceProduct $insuranceProduct): bool
    {
        return $user->can(PermissionEnum::InsuranceProductDelete->value);
    }

}
