<?php

namespace App\Policies\Insurance;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\BancassuranceBeneficiaries;
class BancassuranceCustomersBeneficiariesPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersBeneficiariesView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersBeneficiariesCreate->value);
    }

    public function view(User $user, BancassuranceBeneficiaries $bancassurancecustomersbeneficiaries): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersBeneficiariesView->value);
    }

    public function update(User $user, BancassuranceBeneficiaries $bancassurancecustomersbeneficiaries): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersBeneficiariesUpdate->value);
    }

    public function destroy(User $user, BancassuranceBeneficiaries $bancassurancecustomersbeneficiaries): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersBeneficiariesDelete->value);
    }


}
