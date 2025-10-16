<?php

namespace App\Models\Core\Approval;

use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class WorkflowHistory extends Model
{

    protected $table = 't_WorkFlowHistory';
    protected $primaryKey = 'Id';


    protected $fillable = [
        "Source", "SourceID", "Stage", "Amount", "Notes", "StatusId",
        "CreatedBy", "ModifiedBy", "DeletedBy","isApproved"
    ];

    protected $casts = [
        'Status' => WorkflowStatus::class,
        'IsApproved' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }
    public static function getPrimaryKey(): string
    {
        return 'WorkFlowHistoryId';
    }

    public function status(): Belongsto
    {
        return $this->belongsTo(CodeDetail::class, 'StatusId', 'ID');
    }
    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }
}
