<?php

namespace App\Policies\Insurance;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\BancassuranceCustomersContacts;

class BancassuranceCustomersContactsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersContactsView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersContactsCreate->value);
    }

    public function view(User $user, BancassuranceCustomersContacts $bancassurancecustomercontacts): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersContactsView->value);
    }

    public function update(User $user, BancassuranceCustomersContacts $bancassurancecustomercontacts): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersContactsUpdate->value);
    }

    public function destroy(User $user, BancassuranceCustomersContacts $bancassurancecustomercontacts): bool
    {
        return $user->can(PermissionEnum::BancassuranceCustomersContactsDelete->value);
    }


}
