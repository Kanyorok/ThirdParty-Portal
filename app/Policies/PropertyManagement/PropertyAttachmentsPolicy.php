<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;

class PropertyAttachmentsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyAttachmentsView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyAttachmentsCreate->value);
    }

    public function view(User $user, PropertyAttachments $categoPropertyAttachmentsryMaster): bool
    {
        return $user->can(PermissionEnum::PropertyAttachmentsView->value);
    }

    public function update(User $user, PropertyAttachments $PropertyAttachments): bool
    {
        return $user->can(PermissionEnum::PropertyAttachmentsUpdate->value);
    }

    public function destroy(User $user, PropertyAttachments $PropertyAttachments): bool
    {
        return $user->can(PermissionEnum::PropertyAttachmentsDelete->value);
    }
}
