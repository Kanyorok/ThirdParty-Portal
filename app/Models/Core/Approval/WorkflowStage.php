<?php

namespace App\Models\Core\Approval;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    public function type()
    {
        return $this->belongsTo(WorkflowType::class, 'WorkFlowTypeId', 'Id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'PermissionId', 'id');
    }

    public function status()
    {
        return $this->belongsTo(CodeDetail::class, 'StatusId', 'ID');
    }
  public function workflow()
    {
        return $this->belongsTo(\App\Models\Core\Approval\Workflow::class, 'WorkFlowId', 'Id');
    }

    }
