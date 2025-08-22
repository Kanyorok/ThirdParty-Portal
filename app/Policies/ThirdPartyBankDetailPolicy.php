<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\ThirdParty\ThirdParties;

class ThirdPartyBankDetailPolicy
{
    public function create(User $user, ThirdParties $thirdParty): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->thirdParty && $user->thirdParty->Id === $thirdParty->Id;
    }

    public function viewAny(User $user)
    {
        return $user->isAdmin() || $user->thirdParty;
    }
}
