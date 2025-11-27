<?php

namespace App\Models\Core\Approval;

use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowHistory extends Model
{
 use SoftDeletes;

    protected $table = 't_WorkFlowHistory';
    protected $primaryKey = 'Id';

    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        "Source", "SourceID", "Stage", "Amount", "Notes", "StatusId",
        "CreatedBy", "ModifiedBy", "DeletedBy","isApproved" 
    ];

    protected $casts = [
        'StatusId' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'Amount' => 'decimal:4', // Cast for decimal
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

        public function source(): MorphTo
    {
        return $this->morphTo('source', 'Source', 'SourceID');
    }


   
    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    public function status(): BelongsTo 
    {
        return $this->belongsTo(CodeDetail::class, 'StatusId', 'ID');
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'Stage', 'Order');
    }

}
