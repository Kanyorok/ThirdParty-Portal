<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\Inventory\ItemMasterList;
use App\Models\ThirdParty\Supplier;
use App\Models\Core\Approval\WorkflowHistory;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_Orders';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'OrderID';
    }

    protected $fillable = [
        'OrderNo',
        'OrderDate',
        'Terms',
        'Priority',
        'ExtOrdNum', // RFQ Reference
        'AccountID', // Supplier ID
        'BranchID',
        'Status',
        'OriginationType', // 'rfq', 'contract', 'award', 'direct_procurement'
        'OriginationRef', // Reference to the originating record (Contract ID, Award ID, Plan ID)
        'ContractRef', // For contract-based LPOs
        'AwardRef', // For award-based LPOs
        'PlanRef', // For direct procurement LPOs
        'TotalAmount',
        'Notes',
        'DeliveryTerms',
        'CreatedBy',
        'ModifiedBy'
    ];

    protected $casts = [
        'OrderDate' => 'date',
        'TotalAmount' => 'decimal:2',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'AccountID' => 'integer',
        'BranchID' => 'integer'
    ];

    // **RELATIONSHIPS**

    /**
     * Get the supplier for this order
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'AccountID', 'Id');
    }

    /**
     * Get the order lines for this order
     */
    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLines::class, 'iOrderID', 'Id');
    }

    /**
     * Get the contract if this is a contract-based LPO
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(TenderAward::class, 'ContractRef', 'Id');
    }

    /**
     * Get the award if this is an award-based LPO
     */
    public function award(): BelongsTo
    {
        return $this->belongsTo(TenderAward::class, 'AwardRef', 'Id');
    }

    /**
     * Get the procurement plan if this is a direct procurement LPO
     */
    public function procurementPlan(): BelongsTo
    {
        return $this->belongsTo(ConsolidatedProcurementPlan::class, 'PlanRef', 'PlanID');
    }

    /**
     * Get the user who created this order
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    // **HELPER METHODS**

    /**
     * Check if this is a contract-based LPO
     */
    public function isContractBased(): bool
    {
        return $this->OriginationType === 'contract' && !empty($this->ContractRef);
    }

    /**
     * Check if this is an award-based LPO
     */
    public function isAwardBased(): bool
    {
        return $this->OriginationType === 'award' && !empty($this->AwardRef);
    }

    /**
     * Check if this is a direct procurement LPO
     */
    public function isDirectProcurement(): bool
    {
        return $this->OriginationType === 'direct_procurement' && !empty($this->PlanRef);
    }

    /**
     * Check if this is an RFQ-based LPO (existing system)
     */
    public function isRFQBased(): bool
    {
        return $this->OriginationType === 'rfq' && !empty($this->ExtOrdNum);
    }

    /**
     * Get the origination type display name
     */
    public function getOriginationTypeDisplayName(): string
    {
        return match ($this->OriginationType) {
            'contract' => 'Contract-Based',
            'award' => 'Award-Based',
            'direct_procurement' => 'Direct Procurement',
            'rfq' => 'RFQ-Based',
            default => 'Unknown'
        };
    }

    /**
     * Get the origination reference display
     */
    public function getOriginationReferenceDisplay(): string
    {
        return match ($this->OriginationType) {
            'contract' => $this->contract?->ContractRef ?? 'N/A',
            'award' => $this->award?->tender?->TenderNo ?? 'N/A',
            'direct_procurement' => $this->procurementPlan?->Title ?? 'N/A',
            'rfq' => $this->ExtOrdNum ?? 'N/A',
            default => 'N/A'
        };
    }

    /**
     * Generate a unique LPO number
     */
    public static function generateLPONumber(): string
    {
        $lastOrder = static::latest('Id')->first();
        $nextId = ($lastOrder?->Id ?? 0) + 1;
        return 'LPO-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for filtering by origination type
     */
    public function scopeByOriginationType($query, string $type)
    {
        return $query->where('OriginationType', $type);
    }

    /**
     * Scope for active orders
     */
    public function scopeActive($query)
    {
        return $query->whereNull('DeletedOn');
    }

    /**
     * Recalculate and update the total amount based on order lines
     */
    public function recalculateTotalAmount(): void
    {
        $total = $this->orderLines()->sum('LineTotal');
        $this->update(['TotalAmount' => $total]);
    }

    /**
     * Workflow history relationship
     */
    public function workflowHistory()
    {
        // ApprovalWorkflowService uses the table name (t_Orders) as Source, not the morph alias
        return $this->hasMany(\App\Models\Core\Approval\WorkflowHistory::class, 'SourceID', 'Id')
            ->where('Source', $this->getTable())
            ->whereNull('DeletedOn');
    }

     /**
     * Check if order is approved
     */
    public function isApproved(): bool
    {
        return $this->DocStatus === 'a';
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return $this->DocStatus === 'p' || $this->DocStatus === null;
    }

    /**
     * Check if order is rejected
     */
    public function isRejected(): bool
    {
        return $this->DocStatus === 'r';
    }

    /**
     * Scope for approved orders
     */
    public function scopeApproved($query)
    {
        return $query->where('DocStatus', 'a');
    }

    /**
     * Scope for pending orders
     */
    public function scopePending($query)
    {
        return $query->where(function($q) {
            $q->where('DocStatus', 'p')
              ->orWhereNull('DocStatus');
        });
    }

}
