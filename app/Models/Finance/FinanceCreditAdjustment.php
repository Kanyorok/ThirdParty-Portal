<?php

namespace App\Models\Finance;

use App\Models\ThirdParty\ThirdParties;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceCreditAdjustment extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FinanceCreditAdjustments';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'CreditID',
        'CustomerID',
        'AdjustmentType', // 'increase', 'decrease', 'revision'
        'Amount',
        'NewCreditLimit', // calculated field
        'Reason',
        'ReferenceType', // 'credit_review', 'customer_request', 'business_growth', 'risk_assessment'
        'ReferenceID',
        'RequestedBy',
        'ApprovalStatus', // 'draft', 'approved', 'rejected'
        'ApprovalReason',
        'ApprovedBy',
        'ApprovedOn',
        'EffectiveFrom',
        'Notes',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn'
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'NewCreditLimit' => 'decimal:2',
        'EffectiveFrom' => 'date',
        'ApprovedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceCreditAdjustmentId';
    }

    // Relationships
    public function creditProfile()
    {
        return $this->belongsTo(FinanceCreditManagement::class, 'CreditID', 'Id');
    }

    public function customer()
    {
        return $this->belongsTo(ThirdParties::class, 'CustomerID', 'Id');
    }

    public function requestedByUser()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'RequestedBy', 'Id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'ApprovedBy', 'Id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('ApprovalStatus', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('ApprovalStatus', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('ApprovalStatus', 'rejected');
    }

    // Helpers
    public function isApproved(): bool
    {
        return $this->ApprovalStatus === 'approved';
    }

    public function isPending(): bool
    {
        return $this->ApprovalStatus === 'draft';
    }

    public function isRejected(): bool
    {
        return $this->ApprovalStatus === 'rejected';
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->ApprovalStatus) {
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'draft' => 'bg-warning text-dark',
            default => 'bg-secondary'
        };
    }

    public function getAdjustmentTypeLabel(): string
    {
        return match ($this->AdjustmentType) {
            'increase' => 'Credit Increase',
            'decrease' => 'Credit Decrease',
            'revision' => 'Credit Revision',
            default => 'Unknown'
        };
    }
}

