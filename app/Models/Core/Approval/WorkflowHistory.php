<?php

namespace App\Models\Core\Approval;

use App\Enums\WorkflowStatus;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowHistory extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_WorkFlowHistory';
    protected $primaryKey = 'Id';


    protected $fillable = [
        "Source", "SourceID", "Stage", "Amount", "Notes", "StatusId",
        "CreatedBy", "ModifiedBy", "DeletedBy"
    ];

    protected $casts = [
        'Status' => WorkflowStatus::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'WorkFlowHistoryId';
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'StatusId', 'ID');
    }
}
