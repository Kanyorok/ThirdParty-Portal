<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Inventory\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StorePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::StoreView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Store $storeItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Store $storeItem): bool
    {
        return $user->can(PermissionEnum::StoreCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Store $storeItem): bool
    {
        return $user->can(PermissionEnum::StoreUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user, Store $storeItem): bool
    {
        return $user->can(PermissionEnum::StoreDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, Store $storeItem): bool
    {
        return $user->can(PermissionEnum::StoreRestore->value);
    }

}
