<?php


namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Inventory\ItemMasterList;
use App\Models\Auth\User;
use Illuminate\Auth\Access\Response;

class ItemMasterListPolicy
{
    
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MasterListView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ItemMasterList $itemMasterList): bool
    {
        return $user->can(PermissionEnum::MasterListView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::MasterListCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ItemMasterList $itemMasterList): bool
    {
        return $user->can(PermissionEnum::MasterListUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::MasterListDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ItemMasterList $itemMasterList): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ItemMasterList $itemMasterList): bool
    {
        return false;
    }
}
