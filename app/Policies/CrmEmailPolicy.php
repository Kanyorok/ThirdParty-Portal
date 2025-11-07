<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Communication\Email;
use App\Models\Communication\EmailConversation;

class CrmEmailPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::EmailRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Email $crmEmail): bool
    {
        if ($crmEmail->CreatedBy === $user->Id) {
            return true;
        }

        if ($crmEmail->conversation instanceof EmailConversation) {
            return  $user->can('view', $crmEmail->conversation);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::EmailAssign->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Email $crmEmail): bool
    {
        if ($crmEmail->CreatedBy === $user->Id) {
            return true;
        }

        if ($crmEmail->conversation instanceof EmailConversation) {
            return  $user->can('update', $crmEmail->conversation);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Email $crmEmail): bool
    {
        if ($crmEmail->CreatedBy === $user->Id) {
            return true;
        }

        if ($crmEmail->conversation instanceof EmailConversation) {
            return  $user->can('delete', $crmEmail->conversation);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Email $crmEmail): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Email $crmEmail): bool
    {
        return false;
    }
}
