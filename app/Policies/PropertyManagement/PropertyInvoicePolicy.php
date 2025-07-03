<?php

namespace App\Policies\PropertyManagement;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\PropertyManagement\PropertyInvoice;

class PropertyInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyInvoiceView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyInvoiceCreate->value);
    }

    public function view(User $user, PropertyInvoice $PropertyInvoice): bool
    {
        return $user->can(PermissionEnum::PropertyInvoiceView->value);
    }

    public function update(User $user, PropertyInvoice $PropertyInvoice): bool
    {
        return $user->can(PermissionEnum::PropertyInvoiceUpdate->value);
    }

    public function destroy(User $user, PropertyInvoice $PropertyInvoice): bool
    {
        return $user->can(PermissionEnum::PropertyInvoiceDelete->value);
    }
}