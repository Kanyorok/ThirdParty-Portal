<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\CategoryMaster;

class PropertyCategoryPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        //return $user->can(PermissionEnum::PropertyCategoryView->value);
        return true;
    }

    public function store(User $user): bool
    {
        //return $user->can(PermissionEnum::PropertyCategoryCreate->value);
        return true;
    }

    public function view(User $user, CategoryMaster $categoryMaster): bool
    {
        //return $user->can(PermissionEnum::PropertyCategoryView->value);
        return true;
    }

    public function update(User $user, CategoryMaster $categoryMaster): bool
    {
        //return $user->can(PermissionEnum::PropertyCategoryUpdate->value);
        return true;
    }

    public function destroy(User $user, CategoryMaster $categoryMaster): bool
    {
        //return $user->can(PermissionEnum::PropertyCategoryDelete->value);
        return true;
    }
}

