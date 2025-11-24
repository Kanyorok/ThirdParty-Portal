<?php

namespace App\Models\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use App\Models\Auth\User;
use App\Models\Core\Workflow;
use App\Models\Inventory\ItemMasterList;
use App\Models\Procurement\PlanLineItem;
use App\Models\Procurement\TenderItems;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsolidatedProcurementPlan extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'ConsolidatedProcurementPlanID';
    }
    protected $dates = [
        'CreatedDate',
        'SubmittedDate'
    ];

    protected $casts = [
        'Status' => ProcurementPlanStatusEnum::class,
    ];

    public function workflows()
    {
        return $this->morphMany(Workflow::class, 'source', 'Source', 'SourceID');
    }

    // Relationship with User for CreatedBy
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

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }
    public function lineItems()
    {
        return $this->hasMany(PlanLineItem::class, 'PlanID');
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
