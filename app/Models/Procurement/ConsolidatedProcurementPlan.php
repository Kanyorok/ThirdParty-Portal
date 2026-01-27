<?php

namespace App\Models\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\Approval\WorkflowPending;
use App\Models\Core\Workflow;
use App\Models\Inventory\ItemMasterList;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsolidatedProcurementPlan extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_ConsolidatedProcurementPlan';
    protected $primaryKey = 'PlanID';

    protected $fillable = [
        'Title',
        'ReferenceNumber',
        'FiscalYear',
        'Status',
        'CreatedBy',
        'CreatedDate',
        'SubmittedBy',
        'SubmittedDate',
        'CurrentApprLevel',
        'Remarks',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'PlanID';
    }

    protected $dates = [
        'CreatedDate',
        'SubmittedDate',
        'CreatedOn',
        'ModifiedOn',
        'DeletedOn',
    ];

    protected $casts = [
        'Status' => ProcurementPlanStatusEnum::class,
    ];

    public function workflows()
    {
        return $this->morphMany(
            Workflow::class,
            'source',
            'Source',   // morph type column
            'SourceID'  // morph id column
        );
    }

    // Workflow relationships - commented out as not needed for approval workflow
    // The approval workflow uses t_WorkFlowHistory and t_WorkFlowPending tables directly
    // public function workflows(): MorphMany
    // {
    //     return $this->morphMany(Workflow::class, 'source', 'Source', 'SourceID');
    // }

    /**
     * Get workflow history for this plan
     */
    public function workflowHistory()
    {
        return $this->hasMany(WorkflowHistory::class, 'SourceID', 'PlanID')
            ->where('Source', $this->getTable())
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'desc');
    }

    /**
     * Get pending approvers for this plan
     */
    public function workflowPending()
    {
        return $this->hasMany(WorkflowPending::class, 'SourceID', 'PlanID')
            ->where('Source', $this->getTable())
            ->whereNull('DeletedOn');
    }

    /**
     * Get total amount from line items (for amount-based workflow routing)
     * This is used by the workflow service to determine routing
     */
    public function getAmountAttribute(): float
    {
        // If lineItems are already loaded, use them
        if ($this->relationLoaded('lineItems')) {
            return $this->lineItems->sum(function ($item) {
                $quantity = $item->MergedQty ?? $item->OriginalQTY ?? 0;
                $unitCost = ($item->AdjustedCost > 0)
                    ? $item->AdjustedCost
                    : ($item->EstimatedUnitCost ?? 0);

                return $quantity * $unitCost;
            });
        }

        // DB query fallback
        return (float) $this->lineItems()
            ->selectRaw('SUM((COALESCE(MergedQty, OriginalQTY, 0)) * (CASE WHEN COALESCE(AdjustedCost, 0) > 0 THEN AdjustedCost ELSE COALESCE(EstimatedUnitCost, 0) END)) as total')
            ->value('total') ?? 0.0;
    }

    // User relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'SubmittedBy', 'Id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    // Line items relationship
    public function lineItems()
    {
        return $this->hasMany(PlanLineItem::class, 'PlanID', 'PlanID');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

    /**
     * Scope to get plans pending approval for a specific user
     */
    public function scopePendingApprovalFor($query, int $userId)
    {
        return $query->whereHas('workflowPending', function ($q) use ($userId) {
            $q->where('UserId', $userId);
        })->where('Status', ProcurementPlanStatusEnum::Pending);
    }

    /**
     * Determine if any line item from this consolidated procurement plan
     * has already been attached to a tender (i.e. converted/used).
     * Used by tender initiation view to hide already consumed plans.
     */
    public function isUsed(): bool
    {
        // Cache the result per instance to avoid N+1 queries in loops
        if (array_key_exists('is_used_cached', $this->attributes)) {
            return (bool)$this->attributes['is_used_cached'];
        }

        $lineItemIds = PlanLineItem::where('PlanID', $this->PlanID)->pluck('LineItemID');
        $used = $lineItemIds->isNotEmpty() && TenderItems::whereIn('PlanItemID', $lineItemIds)->exists();
        $this->attributes['is_used_cached'] = $used ? 1 : 0;

        return $used;
    }
}
