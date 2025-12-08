<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\Inventory\ItemMasterList;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Core\Approval\CodeDetail;



class PlanLineItem extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_PlanLineItem';
    protected $primaryKey = 'LineItemID';

    public static function getPrimaryKey(): string
    {
        return 'LineItemId';
    }
    protected $fillable = [
        'PlanID',
        'ItemID',
        'CategoryID',
        'BranchID',
        'DepartmentID',
        'MergedQty',
        'UnitOfMeasure',
        'EstimatedUnitCost',
        'AdjustedCost',
        'ProcurementMethod',
        'SchedulePeriod',
        'ExecutionStatus',
        'BudgetLineID',
        'ChangeRemarks',
        'IsDeleted',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'ExpectedDeliveryDate',
        'SourceType',
        'OriginalQTY',

    ];

    protected $dates = ['ExpectedDeliveryDate'];

    // Relationships
    public function consolidatedProcurementPlan()
    {
        return $this->belongsTo(ConsolidatedProcurementPlan::class, 'PlanID');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentID');
    }

    public function procurementMode()
    {
        return $this->belongsTo(CodeDetail::class, 'ProcurementMethod', 'ID');
    }

    // PlanLineItems.php
    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function departmentNeed()
    {
        return $this->hasOne(DepartmentNeed::class, 'ItemID', 'ItemID')
            ->whereColumn('BranchID', 'BranchID')
            ->whereColumn('DepartmentID', 'DepartmentID');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    public function budgetline()
    {
        return $this->belongsTo(BudgetMaster::class, 'BudgetLineID', 'BudgetLineID');
    }

    public function setMethod()
    {
        return $this->belongsTo(ProcurementMethod::class, 'LineItemID', 'ApprovedPlanLineId');
    }

    public function schedulePlan()
    {
        return $this->belongsTo(SchedulePlan::class, 'LineItemID', 'PlanLineId');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(\App\Models\Procurement\Order::class, 'PlanRef', 'LineItemID');
    }
    /**
     * Get the total amount for this line item
     */
    public function getTotalAmountAttribute(): float
    {
       $quantity = $this->MergedQty ?? $this->OriginalQTY ?? 0;

    // Use AdjustedCost only if it is > 0
    $unitCost = ($this->AdjustedCost > 0)
        ? $this->AdjustedCost
        : ($this->EstimatedUnitCost ?? 0);

    return (float) $quantity * (float) $unitCost;
    }
    public function tenderItems()
    {
        return $this->hasMany(TenderItems::class, 'PlanItemID', 'LineItemID');
    }

}
