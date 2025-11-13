<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Enums\Core\RoleEnum;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Communication\EmailConversation;
use Illuminate\Database\Query\Builder;

class EmailConversationPolicy
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
    public function view(User $user, EmailConversation $emailConversation): bool
    {
        if ($user->can(PermissionEnum::EmailRead->value)) {
            return true;
        }

        if (
            $emailConversation->watchers()->where(function (Builder $query) use ($user) {
                $query->where(function (Builder $query) use ($user) {
                    $query->where('t_EmailConversationUsers.PartyID', $user->Id)->where('t_EmailConversationUsers.Party', User::getPrimaryKey());
                })->orWhere(function (Builder $query) use ($user) {
                    $query->where('t_EmailConversationUsers.PartyID', $user->teams()->select('t_Teams.TeamID'))->where('t_EmailConversationUsers.Party', Team::getPrimaryKey());
                });
            })->exists()
        ) {
            return true;
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
     * Basically for assign.
     */
    public function update(User $user, EmailConversation $emailConversation): bool
    {
        if ($user->can(PermissionEnum::EmailAssign->value)) {
            return true;
        }

        if (
            $emailConversation->watchers()->where(function (Builder $query) use ($user) {
                $query->where(function (Builder $query) use ($user) {
                    $query->where('t_EmailConversationUsers.PartyID', $user->Id)->where('t_EmailConversationUsers.Party', User::getPrimaryKey());
                })->orWhere(function (Builder $query) use ($user) {
                    $query->where('t_EmailConversationUsers.PartyID', $user->teams()->select('t_Teams.TeamID'))->where('t_EmailConversationUsers.Party', Team::getPrimaryKey());
                });
            })->whereIn('t_EmailConversationUsers.Role', [RoleEnum::Admin->value, RoleEnum::Write->value, RoleEnum::Share->value])->exists()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmailConversation $emailConversation): bool
    {
        if ($user->can(PermissionEnum::EmailDelete->value)) {
            return true;
        }

        if (
            $emailConversation->watchers()->where(function (Builder $query) use ($user) {
                $query->where(function (Builder $query) use ($user) {
                    $query->where('t_EmailConversationUsers.PartyID', $user->Id)->where('t_EmailConversationUsers.Party', User::getPrimaryKey());
                })->orWhere(function (Builder $query) use ($user) {
                    $query->where('t_EmailConversationUsers.PartyID', $user->teams()->select('t_Teams.TeamID'))->where('t_EmailConversationUsers.Party', Team::getPrimaryKey());
                });
            })->whereIn('t_EmailConversationUsers.Role', [RoleEnum::Admin->value])->exists()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmailConversation $emailConversation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmailConversation $emailConversation): bool
    {
        return false;
    }
}
