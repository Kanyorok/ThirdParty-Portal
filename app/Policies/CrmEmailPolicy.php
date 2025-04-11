<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\CrmEmail;
use App\Models\EmailConversation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CrmEmailPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CrmEmail $crmEmail): bool
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
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CrmEmail $crmEmail): bool
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
    public function delete(User $user, CrmEmail $crmEmail): bool
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
    public function restore(User $user, CrmEmail $crmEmail): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CrmEmail $crmEmail): bool
    {
        return false;
    }
}
