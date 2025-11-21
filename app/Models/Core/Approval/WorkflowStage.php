<?php

namespace App\Models\Core\Approval;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Settings\WorkFlowLimit;
use App\Models\Settings\WorkFlowType;
use Spatie\Permission\Models\Permission;
use App\Models\Core\CodeDetail;
use App\Models\Settings\Workflow;

class WorkflowStage extends Model
{

    use SoftDeletes;

    protected $table = 't_WorkFlowStages';
    protected $primaryKey = 'Id';


    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';


    protected $fillable = [
        'Order',
        'StageName',
        'EscalationLimit',
        'WorkFlowId',
        'WorkFlowTypeId',
        'WorkFlowLimitId',
        'PermissionId',
        'Count',
        'StatusId',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'Order' => 'integer',
        'EscalationLimit' => 'integer',
        'Count' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Fixed relationships
    public function type_name(): BelongsTo
    {
        return $this->belongsTo(WorkFlowType::class, 'WorkFlowTypeId', 'Id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'PermissionId', 'id');
    }

    public function status()
    {
        return $this->belongsTo(CodeDetail::class, 'StatusId', 'ID');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'WorkFlowId', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return 'WorkFlowStageId';
    }

    public function workflow_limits()
    {
        return $this->hasMany(\App\Models\Settings\WorkFlowLimit::class, 'WorkFlowStageId', 'Id');
    }
}
