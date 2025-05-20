<?php

namespace App\Models\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ProcurementPlan extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ProcurementPlans';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ProcurementPeriodId',
        'ItemId',
        'Category',
        'UOM',
        'PlannedQuarter',
        'ExpectedDeliveryDate',
        'Quantity',
        'TotalCost',
        'CreatedBy',
        'ModifiedBy',
        'Status',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'Quantity' => 'decimal:2',
        'TotalCost' => 'decimal:2',
        'Status' => ProcurementPlanStatusEnum::class,
        'ExpectedDeliveryDate' => 'date',
        'PlannedQuarter' => 'string',
        'Category' => 'string',
        'UOM' => 'string',
    ];

    protected $attributes = [
        'Status' => ProcurementPlanStatusEnum::Draft, // Draft as default val
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemId');
    }

    public function procurementPeriod(): BelongsTo
    {
        return $this->belongsTo(ProcurementPeriod::class, 'ProcurementPeriodId');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('Status', ProcurementPlanStatusEnum::Approved->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('Status', ProcurementPlanStatusEnum::Pending->value);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('Status', ProcurementPlanStatusEnum::Draft->value);
    }

    // helper functions
    public function isDraft(): bool
    {
        return $this->Status === ProcurementPlanStatusEnum::Draft;
    }

    public function isPending(): bool
    {
        return $this->Status === ProcurementPlanStatusEnum::Pending;
    }

    public function isApproved(): bool
    {
        return $this->Status === ProcurementPlanStatusEnum::Approved;
    }
}
