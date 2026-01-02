<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BidOpeningPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BidOpeningRead->value);
    }

    public function create(User $user): bool
    {
        // Permission to START the ceremony
        return $user->can(PermissionEnum::BidOpeningWrite->value);
    }
}
