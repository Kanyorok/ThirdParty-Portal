<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\CRM\ProductDevelopment;
use App\Services\ProductDevService;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductDevelopmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ProductDevelopmentRead->value);
    }

    public function view(User $user, ProductDevelopment $productDevelopment): bool
    {
        if ($productDevelopment->User_ID === $user->Id) {
            return true;
        }

        if ((new ProductDevService($productDevelopment))->commenting() && $user->can(PermissionEnum::ProductDevelopmentRead->value)) { //check if comment
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::ProductDevelopmentWrite->value);
    }

    public function update(User $user, ProductDevelopment $productDevelopment): bool
    {
        if ($productDevelopment->User_ID === $user->Id) {
            return true;
        }

        if ($productDevelopment->User_ID === $user->Id) {
            return true;
        }
        return $user->can(PermissionEnum::ProductDevelopmentUpdate->value);
    }

    public function delete(User $user, ProductDevelopment $productDevelopment): bool
    {
        if ($productDevelopment->User_ID === $user->Id) {
            return true;
        }
        return $user->can(PermissionEnum::ProductDevelopmentDelete->value);
    }

    public function restore(User $user, ProductDevelopment $productDevelopment): bool
    {
        return false;
    }

    public function forceDelete(User $user, ProductDevelopment $productDevelopment): bool
    {
        return false;
    }
}
