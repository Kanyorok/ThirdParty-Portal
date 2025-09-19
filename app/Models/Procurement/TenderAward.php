<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ApprovedBy', 'Id');
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
            'Status' => 'Awarded',
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
}
