<?php

namespace App\Models\Settings;

use App\Models\Core\Approval\WorkflowStage;
use App\Models\Core\Approval\WorkFlowType;
use App\Models\Core\Module;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkFlow extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_WorkFlows';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name',
        'Source',
        'Description',
        'FinalStage',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'FinalStage' => 'string',
    ];

    public static function getPrimaryKey(): string
    {
        return 'WorkFlowId';
    }

    public function type_name()
    {
        return $this->belongsTo(WorkFlowType::class, 'TypeID', 'Id');
    }

    public function stages()
    {
        return $this->hasMany(WorkflowStage::class, 'WorkFlowId', 'Id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'ModuleId');
    }

    public function getIsFinalStageAttribute()
    {
        return ! empty($this->FinalStage);
    }
}
