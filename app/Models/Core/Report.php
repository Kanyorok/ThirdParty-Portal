<?php

namespace App\Models\Core;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Reports';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'Description', 'Path', 'ModuleId', 'ProcedureName', 'PermissionName',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'ModuleId' => 'integer',
    ];

    public static function getPrimaryKey(): string
    {
        return 'ReportId';
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'ModuleID');
    }

    public function scopeAccessibleToUser(Builder $query, User $user): Builder
    {

        return $query->where(function ($q) use ($user) {
            $q->whereNull('PermissionName')
                ->orWhere(function ($subQ) use ($user) {
                    $subQ->whereNotNull('PermissionName')
                        ->where(function ($permQ) use ($user) {
                            // Get permission names from the report's PermissionName column
                            // and check if user has ANY of those permissions
                            $permQ->whereRaw(
                                'LOWER(TRIM(t_Reports.PermissionName)) IN (SELECT LOWER(TRIM(name)) FROM t_Permissions
                                 WHERE EXISTS (
                                    SELECT 1 FROM t_ModelRoles mr
                                    JOIN t_RolePermissions rp ON mr.role_id = rp.role_id
                                    WHERE rp.permission_id = t_Permissions.id
                                    AND mr.model_id = ?
                                    AND mr.model_type = ?
                                 ))',
                                [$user->Id, $user::getPrimaryKey()]
                            );
                        });
                });
        });
    }
}
