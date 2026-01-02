<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\Section;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProcurementSectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementSectionRead->value);
    }

    public function view(User $user, Section $section)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementSectionRead->value);
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementSectionWrite->value);
    }

    public function update(User $user, Section $section)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementSectionUpdate->value);
    }

    public function delete(User $user, Section $section)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementSectionDelete->value);
    }
}
