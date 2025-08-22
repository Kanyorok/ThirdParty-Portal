<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\BancassuranceClaimPayment;

class BancassuranceClaimPaymentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BancassurancePaymentView->value);
    }

    public function store(User $user, BancassuranceClaimPayment $bancassuranceclaimpayment): bool
    {
        return $user->can(PermissionEnum::BancassurancePaymentCreate->value);
    }

    public function view(User $user, BancassuranceClaimPayment $bancassuranceclaimpayment): bool
    {
        return $user->can(PermissionEnum::BancassurancePaymentView->value);
    }

    public function update(User $user, BancassuranceClaimPayment $bancassuranceclaimpayment): bool
    {
        return $user->can(PermissionEnum::BancassurancePaymentUpdate->value);
    }

    public function destroy(User $user, BancassuranceClaimPayment $bancassuranceclaimpayment): bool
    {
        return $user->can(PermissionEnum::BancassurancePaymentDelete->value);
    }
}
