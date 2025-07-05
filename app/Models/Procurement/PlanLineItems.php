<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\Inventory\ItemMasterList;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanLineItems extends Model
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
        return $this->belongsTo(ProcurementMode::class, 'ProcurementMethod', 'id');
    }

    // PlanLineItems.php
    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

    public function createdBy()
    {//todo @edwin by @mureithi
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    public function budgetline()
    {
        return $this->belongsTo(BudgetMaster::class, 'BudgetLineID', 'Id');
    }

    public function setMethod()
    {
        return $this->belongsTo(ProcurementMethod::class, 'LineItemID', 'ApprovedPlanLineId');
    }

    public function schedulePlan()
    {
        return $this->belongsTo(SchedulePlan::class, 'LineItemID', 'PlanLineId');
    }
}
