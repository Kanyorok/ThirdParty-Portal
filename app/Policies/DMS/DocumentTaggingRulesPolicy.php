<?php

namespace App\Policies\DMS;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\DMS\DocumentTaggingRules;

class DocumentTaggingRulesPolicy
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
    public function view(User $user, DocumentTaggingRules $documentTagingRules): bool
    {
        return $user->can(PermissionEnum::DMSView->value);
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
    public function update(User $user, DocumentTaggingRules $documentTagingRules): bool
    {
        return $user->can(PermissionEnum::DMSBulkUpload->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DocumentTaggingRules $documentTagingRules): bool
    {
        return $user->can(PermissionEnum::DMSBulkUpload->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DocumentTaggingRules $documentTagingRules): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DocumentTaggingRules $documentTagingRules): bool
    {
        return false;
    }
}
