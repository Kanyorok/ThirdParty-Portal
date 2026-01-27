<?php

namespace App\Traits\Model;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;

trait RelatedPermissionTrait
{
    public function scopeAccessibleToUser(Builder $query, User $user): Builder
    {
        $column = $this->permissionColum();
        return $query->where(function ($q) use ($column, $user) {
            $q->whereNull($column)
                ->orWhere(function ($subQ) use ($column, $user) {
                    $subQ->whereNotNull($column)
                        ->where(function ($permQ) use ($column, $user) {
                            $permQ->whereRaw(
                                'LOWER(TRIM(' . $this->getTable() . '.' . $column . ')) IN (SELECT LOWER(TRIM(name)) FROM t_Permissions
                                 WHERE EXISTS (
                                    SELECT 1 FROM t_ModelRoles mr
                                    JOIN t_RolePermissions rp ON mr.role_id = rp.role_id
                                    WHERE rp.permission_id = t_Permissions.id
                                    AND mr.model_id = ?
                                    AND mr.model_type = ?
                                    AND mr.BranchId = ?
                                 ))',
                                [$user->Id, $user::getPrimaryKey(), $user->BranchId]
                            );
                        });
                });
        });
    }

    abstract public function permissionColum(): string;
}
