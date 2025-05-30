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
        'ItemCode',
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

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemCode', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOM', 'Id');
    }

    public function branches()
    {
        return $this->belongsTo(Branch::class, 'ToBranch','fromBranch', 'Id');
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

}
