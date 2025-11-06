<?php

namespace App\Models\Core;

use App\Enums\WorkflowStatus;
use App\Traits\Model\UserActorTrait;
use App\Models\Core\Approval\WorkflowType;
use App\Models\Core\Approval\WorkflowStage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Workflows';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Source',
        'WorkflowTypeId',
        'IsFinalStage',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'IsFinalStage' => 'boolean',
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
        return $this->belongsTo(WorkflowType::class, 'WorkflowTypeId');
    }

    public function stages()
    {
        return $this->hasMany(WorkflowStage::class, 'WorkFlowId');
    }
    
     public static function getPrimaryKey(): string
    {
        return 'WorkflowID';
    }


   
}

    