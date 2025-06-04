<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;

class InterBranchRequisition extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_InterBranchRequisition';
    protected $primaryKey = 'Id';
    protected $connection = 'sqlsrv';

    protected $fillable = [
        'ReqNo',
        'FromBranch',
        'ToBranch',
        'UOM',
        'RequestedQty',
        'Remarks',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'Status' => 'boolean',
    ];

   

 

 public function fromBranch()
{
    return $this->belongsTo(Branch::class, 'FromBranch', 'Id');
}


public function toBranch()
{
    return $this->belongsTo(Branch::class, 'ToBranch', 'Id');
}



    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }
    

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }
    public function items()
{
    return $this->hasMany(InterBranchRequisitionItem::class, 'RequisitionId', 'Id');
}

}
