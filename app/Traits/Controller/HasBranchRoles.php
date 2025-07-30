<?php

namespace App\Traits\Controller;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Traits\HasRoles as BaseHasRoles;
use Illuminate\Support\Facades\Auth;

trait HasBranchRoles
{
    use BaseHasRoles {
        assignRole as baseAssignRole;
    }

    public function assignRoleWithBranch($roles, int $branchId, array $extra = [])
    {
        $roles = $this->collectRoles($roles);

        $model = $this->getModel();

        foreach ($roles as $roleId) {
            $this->roles()->syncWithoutDetaching([
                $roleId => array_merge([
                    'BranchId' => $branchId,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ], $extra),
            ]);
        }

        $model->unsetRelation('roles');

        if (config('permission.events_enabled')) {
            event(new \Spatie\Permission\Events\RoleAttached($model, $roles));
        }

        return $this;
    }

    /**
     * Optionally override Spatie's roles() if needed for additional logic.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        )->withPivot(['BranchId', 'CreatedBy', 'ModifiedBy'])->withTimestamps();
    }
}
