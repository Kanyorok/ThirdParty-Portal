<?php

namespace App\Models\CRM\Approval;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendingWorkflow extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_PendingWorkflows_static';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Source', 'SourceID', 'Stage', 'UserId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy', 'DeletedOn',
    ];

    protected $casts = [
        'UserId' => 'integer',
    ];

    public static function getPrimaryKey(): string
    {
        return 'PendingWorkflowID';
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Source", "SourceID");
    }
}
