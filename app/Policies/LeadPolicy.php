<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Enums\Core\RoleEnum;
use App\Enums\LeadStatusEnum;
use App\Models\Auth\User;
use App\Models\CRM\Lead;

class LeadPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->can(PermissionEnum::LeadsManager->value)) {
            return true;
        }

        // Return null to fall through to the policy method
        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::LeadRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lead $lead): bool
    {
        if ($lead->RelationshipManagerID === $user->Id) {
            return true;
        }

        if ($lead->watchers()->where('t_LeadUsers.PartyID', $user->Id)->where('t_LeadUsers.Party', User::getPrimaryKey())->exists()) {
            return true;
        }
        if ($user->can([PermissionEnum::LeadUpdate->value, PermissionEnum::LeadViewAll->value])) {
            return true;
        }

        return ($lead->Status->value === LeadStatusEnum::Won->value && $user->can(PermissionEnum::LeadRead->value));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can([PermissionEnum::LeadWrite->value]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lead $lead): bool
    {
        if ($lead->RelationshipManagerID === $user->Id) {
            return true;
        }

        if (
            $lead->watchers()->where('t_LeadUsers.PartyID', $user->Id)
            ->where('t_LeadUsers.Party', User::getPrimaryKey())->whereIn('Role', [RoleEnum::Write->value, RoleEnum::Admin->value])->exists()
        ) {
            return true;
        }
        return $user->can([PermissionEnum::LeadUpdate->value]);
    }

    /**
     * Determine whether the user can reassign the model.
     */
    public function reassign(User $user, Lead $lead): bool
    {
        if ($lead->RelationshipManagerID === $user->Id) {
            return true;
        }

        if (
            $lead->watchers()->where('t_LeadUsers.PartyID', $user->Id)
            ->where('t_LeadUsers.Party', User::getPrimaryKey())->whereIn('Role', [RoleEnum::Write->value, RoleEnum::Admin->value])->exists()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lead $lead): bool
    {
        if ($lead->RelationshipManagerID === $user->Id) {
            return true;
        }

        if (
            $lead->watchers()->where('t_LeadUsers.PartyID', $user->Id)
            ->where('t_LeadUsers.Party', User::getPrimaryKey())->where('Role', RoleEnum::Admin->value)->exists()
        ) {
            return true;
        }

        return $user->can(PermissionEnum::LeadDelete->value);
    }

    public function share(User $user, Lead $lead): bool
    {
        if ($lead->RelationshipManagerID === $user->Id) {
            return true;
        }
        if (
            $lead->watchers()->where('t_LeadUsers.PartyID', $user->Id)
            ->where('t_LeadUsers.Party', User::getPrimaryKey())->whereIn('Role', [RoleEnum::Admin->value, RoleEnum::Share->value])->exists()
        ) {
            return true;
        }

        return $user->can(PermissionEnum::LeadDelete->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lead $lead): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lead $lead): bool
    {
        return false;
    }
}
