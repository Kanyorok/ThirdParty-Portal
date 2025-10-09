<?php

namespace App\Policies\PropertyManagement;


use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\PropertyManagement\PropertyReceipt;

class PropertyReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyReceiptView->value);
    }
    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyReceiptCreate->value);
    }
    public function view(User $user, PropertyReceipt $PropertyReceipt): bool
    {
        return $user->can(PermissionEnum::PropertyReceiptView->value);
    }
    public function destroy(User $user, PropertyReceipt $PropertyReceipt): bool
    {
        return $user->can(PermissionEnum::PropertyReceiptDelete->value);
    }
    public function print(User $user, PropertyReceipt $PropertyReceipt): bool
    {
        return $user->can(PermissionEnum::PropertyReceiptPrint->value);
    }

}