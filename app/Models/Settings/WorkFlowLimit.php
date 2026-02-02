<?php

namespace App\Models\Settings;

use App\Models\Core\Approval\Permission;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkFlowLimit extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_WorkFlowLimits';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'WorkFlowStageId', 'PermissionId', 'MaxAmount', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    public function workflow_stage()
    {
        return $this->belongsTo(WorkFlowStage::class, 'WorkFlowStageId', 'Id');
    }

    /**
     * Relationship with Permission
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'PermissionId', 'id');
    }

    /**
     * Scope to get only non-deleted records
     */
    public function scopeActive($query)
    {
        return $query->whereNull('DeletedOn');
    }
}
