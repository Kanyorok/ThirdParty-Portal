<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProcurementConfigurationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementConfigRead->value);
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementConfigRead->value);
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementConfigWrite->value);
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementConfigUpdate->value);
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementConfigDelete->value);
    }
}
