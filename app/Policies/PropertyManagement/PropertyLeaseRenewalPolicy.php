<?php

namespace App\Policies\PropertyManagement;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\PropertyManagement\PropertyLeaseRenewal;

class PropertyLeaseRenewalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseRenewalView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseRenewalCreate->value);
    }

    public function view(User $user, PropertyLeaseRenewal $PropertyLeaseRenewal): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseRenewalView->value);
    }

    public function update(User $user, PropertyLeaseRenewal $PropertyLeaseRenewal): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseRenewalUpdate->value);
    }

    public function destroy(User $user, PropertyLeaseRenewal $PropertyLeaseRenewal): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseRenewalDelete->value);
    }

    public function approve(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseRenewalApproval->value);
    }

    public function reject(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyLeaseRenewalApproval->value);
    }

}
