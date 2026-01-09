<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\ApprovalGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApprovalSetupPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::ApprovalSetupRead->value);
    }

    public function view(User $user, ApprovalGroup $approvalGroup)
    {
        return $user->hasPermissionTo(PermissionEnum::ApprovalSetupRead->value);
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::ApprovalSetupWrite->value);
    }

    public function update(User $user, ApprovalGroup $approvalGroup)
    {
        return $user->hasPermissionTo(PermissionEnum::ApprovalSetupUpdate->value);
    }

    public function delete(User $user, ApprovalGroup $approvalGroup)
    {
        return $user->hasPermissionTo(PermissionEnum::ApprovalSetupDelete->value);
    }
}
