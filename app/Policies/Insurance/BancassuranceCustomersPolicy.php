<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\BancassuranceCustomers;

class BancassuranceCustomersPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersCreate->value);
    }

    public function view(User $user, BancassuranceCustomers $bancassurancecustomer): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersView->value);
    }

    public function update(User $user, BancassuranceCustomers $bancassurancecustomer): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersUpdate->value);
    }

    public function destroy(User $user, BancassuranceCustomers $bancassurancecustomer): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersDelete->value);
    }
}
