<?php

namespace App\Policies\procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeeds;

class DepartmentNeedsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }
    public function store (User $user): bool{
        return true;
    }
    public function view(User $user,DepartmentNeeds $departmentNeeds): bool
    {
        return true;
    }
    public function update(User $user, DepartmentNeeds $departmentNeeds):bool
    {
        return true;
    }
    public function destroy(User $user, DepartmentNeeds $departmentNeeds): bool
    {
        return true;
    }
}
