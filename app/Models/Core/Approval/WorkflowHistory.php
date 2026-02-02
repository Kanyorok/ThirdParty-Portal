<?php

namespace App\Models\Core\Approval;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowHistory extends Model
{
    use SoftDeletes;

    protected $table = 't_WorkFlowHistory';
    protected $primaryKey = 'Id';


    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        "Source", "SourceID", "Stage", "Amount", "Notes", "StatusId",
        "CreatedBy", "ModifiedBy", "DeletedBy","isApproved",
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
