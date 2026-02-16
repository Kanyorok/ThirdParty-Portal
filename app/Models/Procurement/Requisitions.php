<?php

namespace App\Models\Procurement;

use App\Enums\WorkflowStatus;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\Approval\WorkflowPending;
use App\Models\Core\Branch;
use App\Models\Inventory\TransactionTransfer;
use App\Services\Procurement\Requisition\RequisitionWorkflowService;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;

class Requisitions extends Model
{
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Requisitions';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'RequisitionID';
    }

    protected $fillable = [
        'RequisitionNo',
        'Branch',
        'Department',
        'NeededBy',
        'Remarks',
        'Category',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CategoryId',
    ];

    protected $casts = [
        // 'Status' => CampaignStatusEnum::class,
        // 'Type' => CampaignTypeEnum::class,
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer', //,
        // 'Processing' => 'boolean'
    ];

    public function requisitionLines()
    {
        return $this->hasMany(RequisitionLine::class, 'RequisitionID', 'Id');
    }

    public function procurementPlan()
    {
        return $this->belongsTo(\App\Models\Procurement\ConsolidatedProcurementPlan::class, 'PlanRef', 'PlanID');
    }

    public function transfer()
    {
        return $this->hasOne(TransactionTransfer::class, 'RequisitionId', 'Id');
    }

    // Workflow status helpers
    public function isPendingApproval(): bool
    {
        return in_array($this->Status, [
            WorkflowStatus::Submitted,
            WorkflowStatus::Pending,
            WorkflowStatus::UnderReview,
        ]);
    }

    public function isApproved(): bool
    {
        return in_array($this->Status, [
            WorkflowStatus::APPROVED,
            WorkflowStatus::Accepted,
            WorkflowStatus::Completed,
        ]);
    }

    public function isRejected(): bool
    {
        return in_array($this->Status, [
            WorkflowStatus::REJECTED,
            WorkflowStatus::RejectedCancel,
        ]);
    }

    public function statusDetail()
    {
        return $this->belongsTo(\App\Models\Core\Approval\CodeDetail::class, 'StatusID', 'ID');
    }

    public function getStatusAttribute()
    {
        return $this->statusDetail?->Value;
    }

    // Auto-submit for approval when created
    protected static function boot()
    {
        parent::boot();

        static::created(function (Requisitions $requisition) {
            // Reload to get status relationship
            $requisition->load('statusDetail');

            if ($requisition->isPendingApproval()) {
                // Use the workflow service to submit for approval
                $workflowService = app(RequisitionWorkflowService::class);
                $workflowService->submit($requisition, $requisition->creator, 'Initial submission');
            }
        });
    }

    /**
    * Workflow history relationship
    */
    public function workflowHistory()
    {
        return $this->morphMany(
            WorkflowHistory::class,
            'source',
            'Source',  // The morph type column in t_WorkFlowHistory
            'SourceID', // The morph id column
            'Id'        // Local key
        );
    }

    public function pendingWorkflows()
    {
        return $this->morphMany(
            WorkflowPending::class,
            'source',
            'Source',  // The morph type column in t_WorkFlowPending
            'SourceID', // The morph id column
            'Id'        // Local key
        );
    }

    public function requestingBranch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id');
    }
}
