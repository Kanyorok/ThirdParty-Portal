<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\HRM\Department;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Procurement\PlanLineItems;
use App\Models\Core\Branch;
use App\Models\Procurement\BudgetMaster;
use App\Models\Inventory\UnitOfMeasure;

class PlanLineItems extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_PlanLineItem';
    protected $primaryKey = 'LineItemID';

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

    // PlanLineItems.php
    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemID', 'Id');
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
  public function budgetLine(){
        return $this->belongsTo(BudgetMaster::class, 'BudgetLineID','BudgetLineID');
    }
    public function setMethod(){
        return $this->belongsTo(ProcurementMethod::class,'LineItemID','ApprovedPlanLineId');
    }
    public function schedulePlan()
    {
        return $this->belongsTo(BudgetMaster::class, 'BudgetLineID', 'BudgetLineID');
    }
}