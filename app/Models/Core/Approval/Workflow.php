<?php

namespace App\Models\Core\Approval;
use App\Enums\WorkflowStatus;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $table = 't_Workflows';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Source',
        'WorkflowTypeId',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Soft-delete style check
    public function isDeleted(): bool
    {
        return !is_null($this->DeletedOn);
    }

    // === Relationships ===

    public function type()
    {
        return $this->belongsTo(WorkflowType::class, 'Id');
    }

    public function stage()
    {
        return $this->belongsTo(WorkFlowStage::class, 'stage', 'order');
    }
    

}
