<?php

namespace App\Models\Core\Approval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowEscalation extends Model
{
    use SoftDeletes;

    protected $table = 't_WorkFlowEscalation';
    protected $primaryKey = 'Id';
    public $timestamps = false; // Disable Laravel timestamps

    // SoftDeletes expects 'deleted_at' by default — map to your custom column
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'WorkFlowStageId',
        'UserId',
        'SupervisorId',
        'Notes',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    /**
     * The workflow stage this escalation belongs to.
     */
    public function workflowStage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'WorkFlowStageId', 'Id');
    }

    public function pending()
    {
        return $this->belongsTo(WorkflowPending::class, 'PendingID');
    }

    /**
     * The user who is escalated.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'UserId', 'Id');
    }

    /**
     * The supervisor to whom the escalation is directed.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'SupervisorId', 'Id');
    }

    /**
     * User who created the escalation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'CreatedBy', 'Id');
    }

    /**
     * User who last modified the escalation.
     */
    public function modifier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'ModifiedBy', 'Id');
    }
}
