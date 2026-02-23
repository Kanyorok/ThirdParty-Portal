<?php

namespace App\Traits\Controller;

use Spatie\Permission\Traits\HasRoles as BaseHasRoles;

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
                    'CreatedOn' => now(),
                    'ModifiedOn' => now(),
                ], $extra),
            ]);
        }

        $model->unsetRelation('roles');

        if (config('permission.events_enabled')) {
            event(new \Spatie\Permission\Events\RoleAttached($model, $roles));
        }

        return $this;
    }
}
