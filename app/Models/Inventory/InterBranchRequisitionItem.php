<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterBranchRequisitionItem extends Model
{
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_InterBranchRequisitionItems';
    protected $primaryKey = 'Id';
    protected $connection = 'sqlsrv';

    protected $fillable = [
        'RequisitionId',
        'Category',
        'Subcategory',
        'ItemCode',
        'Item',
        'UOM',
        'RequestedQty',
        'ApprovedQty',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    public function requisition()
    {
        return $this->belongsTo(InterBranchRequisition::class, 'RequisitionId', 'Id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'Item', 'Id');
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
