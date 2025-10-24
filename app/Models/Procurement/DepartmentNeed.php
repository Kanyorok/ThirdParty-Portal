<?php

namespace App\Models\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Enums\WorkflowStatus;
use App\Models\Core\Branch;
use App\Models\Core\Approval\Workflow;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\HRM\Department;
use App\Models\Inventory\ItemMasterList;
use App\Services\Procurement\DepartmentNeedsWorkflow;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Exports\NeedsExport;
use App\Models\Core\Approval\WorkflowPending;
use Illuminate\Support\Facades\Log;

class DepartmentNeed extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_DepartmentNeeds';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'NeedID', 'BranchID', 'DepartmentID', 'ItemID', 'RequestedQty', 'EstimatedUnitCost',
        'Justification', 'Status', 'FiscalYear', 'RequestedDate', 'PriorityLevel', 'IsEmergency',
        'CreatedBy', 'ModifiedBy', 'DeletedBy', 'IsUsed',

    ];

    protected $casts = [
        'Status' => DepartmentNeedsEnum::class,];

    public static function getPrimaryKey(): string
    {
        return 'department_needs';
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

     public function workflows(): MorphMany
    {
        return $this->morphMany(
            Workflow::class, 
            'source', 
            'Source', 
            'SourceID', 
            'Id'
        );
    }


     public function pendingWorkflows(): MorphMany
    {
        return $this->morphMany(
            WorkflowPending::class, 
            'source', 
            'Source', 
            'SourceID', 
            'Id'
        );
    }

     public function workflowHistory(): MorphMany
    {
        return $this->morphMany(
            WorkflowHistory::class,
            'source',
            'Source',
            'SourceID',
            'Id'
        )->orderBy('CreatedOn', 'desc');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID');
    }

    // Workflow status helpers
    public function isPendingApproval(): bool
    {
        return in_array($this->Status, [
            Workflowstatus::Submitted,
            WorkflowStatus::Pending,
            WorkflowStatus::UnderReview,
        ]);
    }

    //checlast pending workflow action
    public function latestWorkflowAction()
    {
        return $this->workflowHistory()
            ->with(['creator', 'status', 'stage'])
            ->first();
    }

     /**
     * Check if a specific user has a pending approval for this need
     */
    public function hasPendingApprovalFor(int $userId): bool
    {
        return $this->pendingWorkflows()
            ->where('UserId', $userId)
            ->whereNull('DeletedOn')
            ->exists();
    }

    // Auto-submit for approval when created
       
    protected static function boot()
    {
        parent::boot();

        // Auto-submit for approval when created with pending status
        static::created(function (DepartmentNeed $departmentNeed) {
            if ($departmentNeed->isPendingApproval()) {
                try {
                    Log::info('Auto-submitting department need for approval', [
                        'needId' => $departmentNeed->Id,
                        'status' => $departmentNeed->Status?->value,
                    ]);

                    /** @var DepartmentNeedsWorkflow $workflowService */
                    $workflowService = app(DepartmentNeedsWorkflow::class);
                    $workflowService->submit(
                        $departmentNeed, 
                        $departmentNeed->creator, 
                        'Initial submission'
                    );

                    Log::info('Department need auto-submitted successfully', [
                        'needId' => $departmentNeed->Id,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to auto-submit department need', [
                        'needId' => $departmentNeed->Id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't throw - let the record be created even if workflow submission fails
                }
            }
        });

        // Log status changes
        static::updating(function (DepartmentNeed $departmentNeed) {
            if ($departmentNeed->isDirty('Status')) {
                Log::info('Department need status changing', [
                    'needId' => $departmentNeed->Id,
                    'oldStatus' => $departmentNeed->getOriginal('Status'),
                    'newStatus' => $departmentNeed->Status?->value,
                ]);
            }
        });
    }
    

}
