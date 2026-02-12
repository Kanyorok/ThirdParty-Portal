<?php

namespace App\Models\Procurement;
use App\Models\Core\Approval\WorkflowHistory;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQAward extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_RFQAward';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQId',
        'SupplierId',
        'Comments',
        'CreatedBy',
        'ModifiedBy',
        'AwardStatus',
        'AwardDate',
        'AwardedAmount',
        // Contract Management Fields
        'ContractStatus',
        'ContractRef',
        'ContractValue',
        'ContractRequestRef',
        'PaymentTerms',
        'DeliveryTerms',
        'SpecialConditions',
        'ContractApprovalRemarks',
        'ContractApprovedBy',
        'ContractApprovedOn',
        'ContractStartDate',
        'ContractEndDate',
    ];

    protected $casts = [
        'AwardDate' => 'date',
        'ContractStartDate' => 'date',
        'ContractEndDate' => 'date',
        'ContractApprovedOn' => 'datetime',
        'AwardedAmount' => 'decimal:2',
        'ContractValue' => 'decimal:2',
    ];

    public static function getPrimaryKey(): string
    {
        return 'rfq_award';
    }

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQId', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\ThirdParies\Supplier::class, 'SupplierId', 'Id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ContractMilestone::class, 'ContractSourceID', 'Id')
            ->where('ContractSourceType', 'rfq');
    }

    public function penaltyRules(): HasMany
    {
        return $this->hasMany(ContractPenaltyRule::class, 'ContractSourceID', 'Id')
            ->where('ContractSourceType', 'rfq');
    }

     /**
     * Workflow history relationship
     */
    public function workflowHistory()
    {
        return $this->morphMany(
            WorkflowHistory::class,
            'source',
            'Source',
            'SourceID',
            'Id'
        );
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return match ($this->AwardStatus) {
            'Pending' => ['text' => 'Pending', 'class' => 'bg-warning text-dark'],
            'Approved' => ['text' => 'Approved', 'class' => 'bg-success'],
            'Rejected' => ['text' => 'Rejected', 'class' => 'bg-danger'],
            'Cancelled' => ['text' => 'Cancelled', 'class' => 'bg-secondary'],
            default => ['text' => 'Unknown', 'class' => 'bg-light text-dark'],
        };
    }

    public function getContractStatusBadgeAttribute()
    {
        return match ($this->ContractStatus) {
            'Draft Created', 'Dr' => ['text' => 'Draft', 'class' => 'bg-info'],
            'Under Review', 'rv' => ['text' => 'Under Review', 'class' => 'bg-warning text-dark'],
            'Approved', 'Ap' => ['text' => 'Contract Approved', 'class' => 'bg-success'],
            'Sent to Legal' => ['text' => 'With Legal', 'class' => 'bg-primary'],
            'Executed' => ['text' => 'Executed', 'class' => 'bg-dark'],
            'Terminated' => ['text' => 'Terminated', 'class' => 'bg-danger'],
            'Rejected', 'Re' => ['text' => 'Rejected', 'class' => 'bg-danger'],
            default => ['text' => 'Pending Contract', 'class' => 'bg-secondary'],
        };
    }

    public function hasContract()
    {
        return !empty($this->ContractStatus);
    }
}

