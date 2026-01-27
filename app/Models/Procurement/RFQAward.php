<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\Core\Approval\WorkflowHistory;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RFQAward extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_RFQAward';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    // Award Status Constants (matching TenderAward)
    const STATUS_PENDING = 'Pending';
    const STATUS_SUBMITTED = 'Submitted for Approval';
    const STATUS_UNDER_REVIEW = 'Under Review';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_CANCELLED = 'Cancelled';

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
        return $this->belongsTo(User::class, 'ApprovedBy', 'Id');
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
        return !empty($this->ContractStatus) && $this->ContractStatus !== 'Pending Contract';
    }

    public function isContractReady(): bool
    {
        return $this->AwardStatus === self::STATUS_APPROVED && !$this->hasContract();
    }

    // Methods
    public function approve(User $user, ?string $remarks = null): void
    {
        $this->update([
            'AwardStatus' => self::STATUS_APPROVED,
            'ApprovedBy' => $user->Id,
            'ApprovedOn' => now(),
            'ApprovalRemarks' => $remarks,
            'ModifiedBy' => $user->Id,
        ]);
    }

    public function reject(User $user, string $remarks): void
    {
        $this->update([
            'AwardStatus' => self::STATUS_REJECTED,
            'ApprovedBy' => $user->Id,
            'ApprovedOn' => now(),
            'ApprovalRemarks' => $remarks,
            'ModifiedBy' => $user->Id,
        ]);
    }

    public function cancel(User $user, string $reason): void
    {
        $this->update([
            'AwardStatus' => self::STATUS_CANCELLED,
            'ApprovalRemarks' => $reason,
            'ModifiedBy' => $user->Id,
        ]);
    }
}
