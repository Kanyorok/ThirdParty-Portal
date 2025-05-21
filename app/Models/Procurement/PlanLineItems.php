<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'BranchID',
        'DepartmentID',
        'MergedQty',
        'EstimatedUnitCost',
        'AdjustedCost',
        'ProcurementMethod',
        'SchedulePeriod',
        'ExecutionStatus',
        'ChangeRemarks',
        'IsDeleted',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
    protected $dates = ['ExpectedDeliveryDate'];


    // Relationships
   public function consolidatedProcurementPlan()
    {
        return $this->belongsTo(ConsolidatedProcurementPlan::class, 'PlanID');
    }
 public function category()
{
    return $this->belongsTo(ItemCategory::class, 'CategoryID', 'Id');
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
    {
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
}