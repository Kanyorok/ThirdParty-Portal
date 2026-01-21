<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeed;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentNeedPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsRead->value);
    }

    public function view(User $user, DepartmentNeed $departmentNeed): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsRead->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsWrite->value);
    }

    public function update(User $user, DepartmentNeed $departmentNeed): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsUpdate->value);
    }

    public function delete(User $user, DepartmentNeed $departmentNeed): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsDelete->value);
    }

    // Alias for destroy
    public function destroy(User $user, DepartmentNeed $departmentNeed): bool
    {
        return $this->delete($user, $departmentNeed);
    }

    public function approve(User $user, DepartmentNeed $departmentNeed): bool
    {
        return $user->can(PermissionEnum::DepartmentNeedsApproval->value);
    }
}
