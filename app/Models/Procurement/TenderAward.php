<?php

namespace App\Models\Procurement;

use App\Enums\TenderStatusEnum;
use App\Models\Auth\User;
use App\Models\ThirdParies\Supplier;
use App\Models\Core\Approval\WorkflowHistory;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenderAward extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_TenderAwards';
    protected $primaryKey = 'Id';

    // Award Status Enums
    const STATUS_PENDING = 'Pending';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'TenderID',
        'WinningSupplierID',
        'AwardedAmount',
        'AwardJustification',
        'AwardStatus',
        'AwardDate',
        'ContractStartDate',
        'ContractEndDate',
        'TechnicalScore',
        'FinancialScore',
        'TotalScore',
        'NotifyUnsuccessfulBidders',
        'ApprovalRemarks',
        'ApprovedBy',
        'ApprovedOn',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
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
        // Contract Lifecycle Fields
        'TerminationReason',
        'TerminationDate',
        'SettlementDetails',
    ];

    protected $casts = [
        'AwardedAmount' => 'decimal:2',
        'TechnicalScore' => 'decimal:2',
        'FinancialScore' => 'decimal:2',
        'TotalScore' => 'decimal:2',
        'NotifyUnsuccessfulBidders' => 'boolean',
        'AwardDate' => 'date',
        'ContractStartDate' => 'date',
        'ContractEndDate' => 'date',
        'ApprovedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        // Contract Management Casts
        'ContractValue' => 'decimal:2',
        'ContractApprovedOn' => 'datetime',
        'TerminationDate' => 'date',
    ];

    // Relationships
    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class, 'TenderID', 'Id');
    }

    public function winningSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'WinningSupplierID', 'Id');
    }

    /**
     * Get the winning supplier's third party details
     */
    public function winningThirdParty()
    {
        return $this->hasOneThrough(
            \App\Models\ThirdParty\ThirdParties::class,
            \App\Models\ThirdParies\Supplier::class,
            'Id', // Foreign key on suppliers table
            'Id', // Foreign key on third_parties table
            'WinningSupplierID', // Local key on tender_awards table
            'ThirdPartyID' // Local key on suppliers table
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ApprovedBy', 'Id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(\App\Models\Procurement\Order::class, 'AwardRef', 'Id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('AwardStatus', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('AwardStatus', self::STATUS_APPROVED);
    }

    public function scopeForTender($query, $tenderId)
    {
        return $query->where('TenderID', $tenderId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return match ($this->AwardStatus) {
            self::STATUS_PENDING => ['text' => 'Pending', 'class' => 'bg-warning text-dark'],
            self::STATUS_APPROVED => ['text' => 'Approved', 'class' => 'bg-success'],
            self::STATUS_REJECTED => ['text' => 'Rejected', 'class' => 'bg-danger'],
            self::STATUS_CANCELLED => ['text' => 'Cancelled', 'class' => 'bg-secondary'],
            default => ['text' => 'Unknown', 'class' => 'bg-light text-dark'],
        };
    }

    public function getIsApprovedAttribute()
    {
        return $this->AwardStatus === self::STATUS_APPROVED;
    }

    public function getIsPendingAttribute()
    {
        return $this->AwardStatus === self::STATUS_PENDING;
    }

    public function getContractStatusBadgeAttribute()
    {
        return match ($this->ContractStatus) {
            'Draft Created' => ['text' => 'Draft', 'class' => 'bg-info'],
            'Under Review' => ['text' => 'Under Review', 'class' => 'bg-warning text-dark'],
            'Approved' => ['text' => 'Contract Approved', 'class' => 'bg-success'],
            'Sent to Legal' => ['text' => 'With Legal', 'class' => 'bg-primary'],
            'Executed' => ['text' => 'Executed', 'class' => 'bg-dark'],
            'Terminated' => ['text' => 'Terminated', 'class' => 'bg-danger'],
            default => ['text' => 'Pending Contract', 'class' => 'bg-secondary'],
        };
    }

    public function hasContract()
    {
        return !empty($this->ContractStatus) && $this->ContractStatus !== 'Pending Contract';
    }

    public function isContractReady()
    {
        return $this->AwardStatus === self::STATUS_APPROVED && !$this->hasContract();
    }

    // Methods
    public function approve(User $user, ?string $remarks = null)
    {
        $this->update([
            'AwardStatus' => self::STATUS_APPROVED,
            'ApprovedBy' => $user->Id,
            'ApprovedOn' => now(),
            'ApprovalRemarks' => $remarks,
            'ModifiedBy' => $user->Id,
        ]);

        // Update tender status to awarded
        $this->tender->update([
            'Status' => TenderStatusEnum::Awarded,
            'ModifiedBy' => $user->Id,
        ]);
    }

    public function reject(User $user, string $remarks)
    {
        $this->update([
            'AwardStatus' => self::STATUS_REJECTED,
            'ApprovedBy' => $user->Id,
            'ApprovedOn' => now(),
            'ApprovalRemarks' => $remarks,
            'ModifiedBy' => $user->Id,
        ]);
    }
   
    public function cancel(User $user, string $reason)
    {
        $this->update([
            'AwardStatus' => self::STATUS_CANCELLED,
            'ApprovalRemarks' => $reason,
            'ModifiedBy' => $user->Id,
        ]);
    }

    public static function getPrimaryKey(): string
    {
        return 'Id';
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
}
