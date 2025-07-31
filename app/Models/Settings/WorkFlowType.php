<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Settings\ApprovalStage;

class WorkFlowType extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_WorkFlowTypes';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'TypeID', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'WorkFlowTypeId';
    }

    public function approvalStages()
    {
        return $this->hasMany(ApprovalStage::class, 'TypeID', 'Id');
    }
}
