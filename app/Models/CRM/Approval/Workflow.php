<?php

namespace App\Models\CRM\Approval;

use App\Enums\WorkflowStatus;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use UserActorTrait, SoftDeletes;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Workflows_static';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'Source', 'SourceID', 'Stage', 'Status', 'Notes',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'Status' => WorkflowStatus::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'WorkflowID';
    }

}
