<?php

namespace App\Models\Procurement;

use App\Enums\WorkflowStatus;
use App\Models\Core\Approval\WorkflowHistory;
use App\Services\Procurement\Requisition\RequisitionWorkflowService;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Inventory\TransactionTransfer;


class Requisitions extends Model
{
    //

    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
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
        'CategoryId'
    ];

    protected $casts = [
        // 'Status' => CampaignStatusEnum::class,
        // 'Type' => CampaignTypeEnum::class,
        'CreatedBy'  => 'integer',
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

    // Auto-submit for approval when created
    protected static function boot()
    {
        parent::boot();

        static::created(function (Requisitions $requisition) {
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


}
