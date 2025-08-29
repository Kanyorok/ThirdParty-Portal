<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Enums\Core\RoleEnum;
use App\Models\Auth\User;
use App\Models\CRM\Ticket;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::TicketRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($ticket->user($user)->exists()) {
            return true;
        }
        return $user->can(PermissionEnum::TicketUpdate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function approve(User $user, Ticket $ticket): bool
    {
        if ((int) $ticket->ModifiedBy === (int) $user->Id) {
            return false;
        }

        return $user->can(PermissionEnum::TicketApproval->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::TicketWrite->value);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $ticket->userRole($user, [RoleEnum::Admin->value])->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $ticket->userRole($user, [RoleEnum::Write->value, RoleEnum::Share->value, RoleEnum::Admin->value])->exists();
        /*if () {
            return true;
        }
        return $user->can(PermissionEnum::TicketUpdate->value);*/
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $ticket->userRole($user, [RoleEnum::Admin->value])->exists();
        /*if () {
            return true;
        }
        return $user->can(PermissionEnum::TicketDelete->value);*/
    }

    /**
     * Determine whether the user can restore a closed ticket
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->can(PermissionEnum::TicketUpdate->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }
}
