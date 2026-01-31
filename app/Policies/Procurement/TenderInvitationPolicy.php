<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\TenderInvitation;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenderInvitationPolicy
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        if ($user instanceof \App\Models\ThirdParty\ThirdPartyUser) {
            return true;
        }

        return $user->can(PermissionEnum::TenderInvitationRead->value);
    }

    public function view(User $user, TenderInvitation $invitation): bool
    {
        return $user->can(PermissionEnum::TenderInvitationRead->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::TenderInvitationWrite->value);
    }

    public function update(User $user, TenderInvitation $invitation): bool
    {
        return $user->can(PermissionEnum::TenderInvitationUpdate->value);
    }

    public function delete(User $user, TenderInvitation $invitation): bool
    {
        return $user->can(PermissionEnum::TenderInvitationDelete->value);
    }
}
