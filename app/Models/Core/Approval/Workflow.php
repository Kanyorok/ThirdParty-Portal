<?php

namespace App\Models\Core\Approval;
use App\Enums\WorkflowStatus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Workflow extends Model
{
    protected $table = 't_Workflows';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Source',
        'WorkflowTypeId',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Soft-delete style check
    public function isDeleted(): bool
    {
        return !is_null($this->DeletedOn);
    }

    // === Relationships ===

    public function type()
    {
        return $this->belongsTo(WorkflowType::class,'WorkflowTypeId', 'Id');
    }

    public function stage()
    {
        return $this->belongsTo(WorkFlowStage::class, 'stage', 'order');
    }
    
     /**
     * MORPH TO relationship - This connects to DepartmentNeed, etc.
     */
    public function source(): MorphTo
    {
        return $this->morphTo('source', 'Source', 'SourceID');
    }

    // Boot method to handle cascade delete
    protected static function boot()
    {
        parent::boot();

        // When workflow is being deleted, delete all its stages first
        static::deleting(function ($workflow) {
            // Delete all associated stages
            $workflow->stages()->delete();
            
            \Illuminate\Support\Facades\Log::info('Deleted workflow stages during cascade', [
                'workflow_id' => $workflow->Id,
                'stages_deleted' => $workflow->stages()->count(),
            ]);
        });
    }

}
