<?php

namespace App\Policies\Insurance;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\BancassurancePremiumPayments;


class BancassurancePremiumPaymentsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BancassurancePremiumPaymentsView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::BancassurancePremiumPaymentsCreate->value);
    }

    public function view(User $user, BancassurancePremiumPayments $bancassurancePremiumPayments): bool
    {
        return $user->can(PermissionEnum::BancassurancePremiumPaymentsView->value);
    }

    public function update(User $user, BancassurancePremiumPayments $bancassurancePremiumPayments): bool
    {
        return $user->can(PermissionEnum::BancassurancePremiumPaymentsUpdate->value);
    }

    public function destroy(User $user, BancassurancePremiumPayments $bancassurancePremiumPayments): bool
    {
        return $user->can(PermissionEnum::BancassurancePremiumPaymentsDelete->value);
    }


}
