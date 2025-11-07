<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeed;

class DepartmentNeedsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsRead->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsWrite->value);
    }

    public function view(User $user, DepartmentNeed $departmentNeeds): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsRead->value);
    }

    public function update(User $user, DepartmentNeed $departmentNeeds): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsUpdate->value);
    }

    public function destroy(User $user, DepartmentNeed $departmentNeeds): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsDelete->value);
    }

    public function approve(User $user, DepartmentNeed $departmentNeeds): bool
    {
        //dd($departmentNeeds);
        if ($departmentNeeds->Status->value === DepartmentNeedsEnum::Approved->value) {
            return false;
        }

        if ($departmentNeeds->CreatedBy === $user->Id) {
            return false;
        }

        return $user->can(PermissionEnum::DepartmentNeedsApproval->value);
    }
}

