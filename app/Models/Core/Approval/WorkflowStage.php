<?php

namespace App\Models\Core\Approval;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowStage extends Model
{
   
    use SoftDeletes;

    protected $table = 't_WorkFlowStages';

    protected $primaryKey = 'Id';

    const DELETED_AT = 'DeletedOn';

  
    public $timestamps = false; // because you're using custom timestamp columns

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
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $dates = [
        'CreatedOn',
        'ModifiedOn',
        'DeletedOn',
    ];

    // Example relationships (assuming you have models for these)
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'WorkFlowId');
    }

    public function type()
    {
        return $this->belongsTo(WorkflowType::class, 'WorkFlowTypeId');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'PermissionId');
    }
}
