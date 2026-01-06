<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\RFQResponse;
use Illuminate\Auth\Access\HandlesAuthorization;

class RFQResponsePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::RFQResponseRead->value);
    }

    public function view(User $user, RFQResponse $response): bool
    {
        return $user->can(PermissionEnum::RFQResponseRead->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::RFQResponseWrite->value);
    }

    public function update(User $user, RFQResponse $response): bool
    {
        return $user->can(PermissionEnum::RFQResponseUpdate->value);
    }
}
