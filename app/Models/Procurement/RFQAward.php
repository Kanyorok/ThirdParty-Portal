<?php

namespace App\Models\Procurement;

use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQAward extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_RFQAward';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    // Award Status Constants (matching TenderAward)
    public const STATUS_PENDING = 'Pending';
    public const STATUS_SUBMITTED = 'Submitted for Approval';
    public const STATUS_UNDER_REVIEW = 'Under Review';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'RFQId',
        'SupplierId',
        'Comments',
        'CreatedBy',
        'ModifiedBy',
        'AwardStatus',
        'AwardDate',
        'AwardedAmount',
        'AwardJustification',
        'ApprovalRemarks',
        'ApprovedBy',
        'ApprovedOn',
        // Contract Management Fields
        'ContractStatus',
        'ContractRef',
        'ContractValue',
        'ContractTaxID',
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
        'ApprovedOn' => 'datetime',
        'AwardedAmount' => 'decimal:2',
        'ContractValue' => 'decimal:2',
        'ContractTaxID' => 'integer',
    ];

    public static function getPrimaryKey(): string
    {
        return 'rfq_award';
    }

    // Relationships
    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RFQ::class, 'RFQId', 'Id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ThirdParies\Supplier::class, 'SupplierId', 'Id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'ApprovedBy', 'Id');
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

    public function contractTaxRule(): BelongsTo
    {
        return $this->belongsTo(FinanceTaxRuleConfiguration::class, 'ContractTaxID', 'Id');
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

    // Query Scopes
    public function scopePending($query)
    {
        return $query->whereIn('AwardStatus', [self::STATUS_PENDING, self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW]);
    }

    public function scopeApproved($query)
    {
        return $query->where('AwardStatus', self::STATUS_APPROVED);
    }

    public function scopeForRfq($query, $rfqId)
    {
        return $query->where('RFQId', $rfqId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return match ($this->AwardStatus) {
            self::STATUS_PENDING => ['text' => 'Pending', 'class' => 'bg-warning text-dark'],
            self::STATUS_SUBMITTED => ['text' => 'Submitted', 'class' => 'bg-info'],
            self::STATUS_UNDER_REVIEW => ['text' => 'Under Review', 'class' => 'bg-primary'],
            self::STATUS_APPROVED => ['text' => 'Approved', 'class' => 'bg-success'],
            self::STATUS_REJECTED => ['text' => 'Rejected', 'class' => 'bg-danger'],
            self::STATUS_CANCELLED => ['text' => 'Cancelled', 'class' => 'bg-secondary'],
            default => ['text' => 'Unknown', 'class' => 'bg-light text-dark'],
        };
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->AwardStatus === self::STATUS_APPROVED;
    }

    public function getIsPendingAttribute(): bool
    {
        return in_array($this->AwardStatus, [self::STATUS_PENDING, self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW]);
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

    public function hasContract(): bool
    {
        return ! empty($this->ContractStatus) && $this->ContractStatus !== 'Pending Contract';
    }

    /**
     * Approve this RFQ award.
     * Called by AwardsController::approve() after the workflow step succeeds.
     */
    public function approve(\App\Models\Auth\User $user, ?string $remarks = null): bool
    {
        return $this->update([
            'AwardStatus' => self::STATUS_APPROVED,
            'ApprovedBy' => $user->Id,
            'ApprovedOn' => now(),
            'ApprovalRemarks' => $remarks,
            'ModifiedBy' => $user->Id,
            'ModifiedOn' => now(),
        ]);
    }

    /**
     * Reject this RFQ award.
     * Called by AwardsController::reject() after the workflow step succeeds.
     */
    public function reject(\App\Models\Auth\User $user, ?string $reason = null): bool
    {
        return $this->update([
            'AwardStatus' => self::STATUS_REJECTED,
            'ApprovalRemarks' => $reason,
            'ModifiedBy' => $user->Id,
            'ModifiedOn' => now(),
        ]);
    }
}
