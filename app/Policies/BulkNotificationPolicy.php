<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Communication\BulkNotification;

class BulkNotificationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::DebtCollectionView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BulkNotification $bulkNotification): bool
    {
        return $user->can(PermissionEnum::DebtCollectionView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::DebtNotificationSend->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BulkNotification $bulkNotification): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BulkNotification $bulkNotification): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BulkNotification $bulkNotification): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BulkNotification $bulkNotification): bool
    {
        return false;
    }
}
