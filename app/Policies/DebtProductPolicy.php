<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\BR\DebtProduct;
use App\Models\ThirdParies\Board;

class DebtProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::DebtCollectionView->value);
    }

    public function assign(User $user): bool
    {
        return $user->can(PermissionEnum::DebtCollectionAdmin->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DebtProduct $debtProduct): bool
    {
        //check if debt loan is staff
        if (! $user->can(PermissionEnum::Managers->value) && (User::query()->where('ClientID', $debtProduct->ClientID)->exists() || Board::query()->where('ClientID', $debtProduct->ClientID)->exists())) {
            return false;
        }


        return $user->can(PermissionEnum::DebtCollectionView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::DebtCollectionView->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DebtProduct $debtProduct): bool
    {
        return $user->can(PermissionEnum::DebtCollectionView->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DebtProduct $debtProduct): bool
    {
        return $user->can(PermissionEnum::DebtCollectionView->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DebtProduct $debtProduct): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DebtProduct $debtProduct): bool
    {
        return false;
    }
}
