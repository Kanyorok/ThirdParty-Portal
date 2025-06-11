<?php

namespace App\Policies\Inventory;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\Inventory\PriceManagement;
use Illuminate\Auth\Access\Response;

class PriceManagementPolicy
{

        public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PriceManagement $priceManagement): bool
    {
        return $user->can(PermissionEnum::PriceManagementView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::PriceManagementDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, PriceManagement $priceManagement): bool
    {
        return $user->can(PermissionEnum::PriceManagementRestore->value);
    }

}

    

