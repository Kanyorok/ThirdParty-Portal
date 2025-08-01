<?php

namespace App\Models\Settings;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Settings\WorkFlowType;
use App\Models\Settings\WorkflowStage;

class WorkFlow extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_WorkFlows';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name',
        'Source',
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'IsFinalStage' => 'boolean',
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
}
