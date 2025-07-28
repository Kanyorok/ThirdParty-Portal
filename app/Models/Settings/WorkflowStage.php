<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Auth\User;

class WorkflowStage extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_WorkFlows';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'Source', 'Description',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'WorkflowStageId';
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }
}
