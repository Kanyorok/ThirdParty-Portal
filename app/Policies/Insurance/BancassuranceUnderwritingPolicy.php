<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\BancassuranceUnderwriting;

class BancassuranceUnderwritingPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceUnderwritingView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::BancassuranceUnderwritingCreate->value);
    }

    public function view(User $user, BancassuranceUnderwriting $bancassuranceunderwriting): bool
    {
        return $user->can(PermissionEnum::BancassuranceUnderwritingView->value);
    }

    public function update(User $user, BancassuranceUnderwriting $bancassuranceunderwriting): bool
    {
        return $user->can(PermissionEnum::BancassuranceUnderwritingUpdate->value);
    }

    public function destroy(User $user, BancassuranceUnderwriting $bancassuranceunderwriting): bool
    {
        return $user->can(PermissionEnum::BancassuranceUnderwritingDelete->value);
    }
}
