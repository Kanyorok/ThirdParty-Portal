<?php

namespace App\Policies\DMS;

use App\Enums\Core\PermissionEnum;
use App\Enums\Core\RoleEnum;
use App\Models\Auth\User;
use App\Models\DMS\Document;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::DMSView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Document $document): bool
    {
        return Document::query()->user($user)->where('Id', $document->Id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::DMSBulkUpload->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return Document::query()->userRole($user, [RoleEnum::Admin->value, RoleEnum::Share->value, RoleEnum::Write->value])->where('Id', $document->Id)->exists();
    }

    public function admin(User $user, Document $document): bool
    {
        return Document::query()->userRole($user, [RoleEnum::Admin->value])->where('Id', $document->Id)->exists();
    }

    public function share(User $user, Document $document): bool
    {
        return Document::query()->userRole($user, [RoleEnum::Admin->value, RoleEnum::Share->value])->where('Id', $document->Id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return $this->admin($user, $document);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return false;
    }
}
