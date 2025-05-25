<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\ProcurementMethod;

class ProcurementMethodPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ProcurementMethodRead->value);
    }
    public function store (User $user,ProcurementMethod $procurementMethod): bool{
        return $user->can(PermissionEnum::ProcurementMethodWrite->value);
    }
    public function view(User $user,ProcurementMethod $procurementMethod): bool
    {
        return $user->can(PermissionEnum::ProcurementMethodRead->value);
    }
}