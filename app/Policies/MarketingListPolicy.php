<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Enums\Core\VisibilityEnum;
use App\Models\Auth\User;
use App\Models\BR\DebtProduct;
use App\Models\CRM\MarketingList;

class MarketingListPolicy
{
    public function debt(User $user): bool
    {
        return $user->can(PermissionEnum::DebtCollectionLists->value);
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MarketingListRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MarketingList $marketingList): bool
    {
        if ($marketingList->Visibility->value === VisibilityEnum::Private->value && ($marketingList->CreatedBy !== $user->Id)) {
            return false;
        }

        if ($marketingList->CreatedBy === $user->Id) {
            return true;
        }

        return ($marketingList->Source === DebtProduct::getPrimaryKey())
            ? $this->debt($user)
            : $user->can(PermissionEnum::MarketingListUpdate->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::MarketingListWrite->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketingList $marketingList): bool
    {
        if ($marketingList->Visibility->value === VisibilityEnum::Private->value && ($marketingList->CreatedBy !== $user->Id)) {
            return false;
        }

        if ($marketingList->CreatedBy === $user->Id) {
            return true;
        }
        return ($marketingList->Source === DebtProduct::getPrimaryKey())
            ? $this->debt($user)
            : $user->can(PermissionEnum::MarketingListUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketingList $marketingList): bool
    {
        if ($marketingList->Visibility->value === VisibilityEnum::Private->value && ($marketingList->CreatedBy !== $user->Id)) {
            return false;
        }

        if ($marketingList->CreatedBy === $user->Id) {
            return true;
        }

        return $user->can(PermissionEnum::MarketingListDelete->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MarketingList $marketingList): bool
    {
        return false;// return $user->can(PermissionEnum::MarketingListRead->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MarketingList $marketingList): bool
    {
        return false;
    }
}
