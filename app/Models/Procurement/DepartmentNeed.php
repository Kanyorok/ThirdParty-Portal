<?php

namespace App\Models\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Enums\WorkflowStatus;
use App\Models\Core\Branch;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\HRM\Department;
use App\Models\Inventory\ItemMasterList;
use App\Services\Procurement\DepartmentNeedsWorkflow;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Exports\NeedsExport;

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
        return 'DepartmentNeedID';
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

    public function workflows(): MorphMany
    {
        return $this->morphMany(Workflow::class, __FUNCTION__, 'Source', 'SourceID', 'Id');
    }

    public function pendingWorkflows(): MorphMany
    {
        return $this->morphMany(PendingWorkflow::class, __FUNCTION__, 'Source', 'SourceID', 'Id');
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

    // Auto-submit for approval when created
    protected static function boot()
    {
        parent::boot();

        static::created(function (DepartmentNeed $departmentNeed) {
            if ($departmentNeed->isPendingApproval()) {
                /** @var DepartmentNeedsWorkflow $workflowService */
                $workflowService = app(DepartmentNeedsWorkflow::class);
                $workflowService->submit($departmentNeed, $departmentNeed->creator, 'Initial submission');
            }
        });
    }

}
