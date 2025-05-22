<?php

namespace App\Policies\procurement;

use App\Enums\Core\PermissionEnum;
use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeeds;

class DepartmentNeedsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsRead->value);
    }
    public function store (User $user): bool{
        return $user->can(PermissionEnum::DepartmentNeedsWrite->value);
    }
    public function view(User $user,DepartmentNeeds $departmentNeeds): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsRead->value);
    }
    public function update(User $user, DepartmentNeeds $departmentNeeds):bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsUpdate->value);
    }
    public function destroy(User $user, DepartmentNeeds $departmentNeeds): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsDelete->value);
    }
    public function approve(User $user, DepartmentNeeds $departmentNeeds): bool
    {
        //dd($departmentNeeds);
        if ($departmentNeeds->Status->value === DepartmentNeedsEnum::Approval->value) {
            return false;
        }

        if ($departmentNeeds->CreatedBy === $user->Id) {
            return false;
        }

        return $user->can(PermissionEnum::DepartmentNeedsApproval->value);
    }
}

