<?php

namespace App\Models\Settings;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkFlowStage extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_WorkflowStages';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Order',
        'StageName',
        'EscalationLimit',
        'WorkFlowId',
        'WorkFlowTypeId',
        'WorkFlowLimitId',
        'PermissionId',
        'Count',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public function workflow()
    {
        return $this->belongsTo(WorkFlow::class, 'WorkFlowId', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return 'WorkFlowStageId';
    }
}
