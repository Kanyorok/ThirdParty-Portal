<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\TenderAward;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContractPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ContractView->value);
    }

    public function view(User $user, TenderAward $contract): bool
    {
        return $user->can(PermissionEnum::ContractView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::ContractCreate->value);
    }

    public function update(User $user, TenderAward $contract): bool
    {
        return $user->can(PermissionEnum::ContractUpdate->value);
    }

    public function delete(User $user, TenderAward $contract): bool
    {
        return $user->can(PermissionEnum::ContractDelete->value);
    }

    public function approve(User $user, TenderAward $contract): bool
    {
        return $user->can(PermissionEnum::ContractApprove->value);
    }
}
