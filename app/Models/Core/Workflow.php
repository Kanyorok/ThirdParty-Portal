<?php

namespace App\Models\Core;

use App\Models\Core\Approval\WorkflowStage;
use App\Models\Core\Approval\WorkflowType;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
        return ! is_null($this->DeletedOn);
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
