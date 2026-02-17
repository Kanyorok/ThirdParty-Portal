<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\CRM\MarketingListFilter;

class MarketingListFilterPolicy
{
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
    public function view(User $user, MarketingListFilter $marketingListFilter): bool
    {
        if ($marketingListFilter->CreatedBy === $user->Id) {
            return true;
        }

        return $user->can(PermissionEnum::MarketingListUpdate->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::MarketingListRead->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketingListFilter $marketingListFilter): bool
    {
        if ($marketingListFilter->CreatedBy === $user->Id) {
            return true;
        }

        return $user->can(PermissionEnum::MarketingListUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketingListFilter $marketingListFilter): bool
    {
        if ($marketingListFilter->CreatedBy === $user->Id) {
            return true;
        }

        return $user->can(PermissionEnum::MarketingListUpdate->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MarketingListFilter $marketingListFilter): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MarketingListFilter $marketingListFilter): bool
    {
        return false;
    }
}
