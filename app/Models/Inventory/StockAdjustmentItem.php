<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\ItemMasterList;


class StockAdjustmentItem extends Model
{
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    public $timestamps = false;
    protected $table = 't_StockAdjustmentItems';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'AdjustmentId', 'Item', 'AdjustmentQty', 'Remarks', 'UOM', 'UnitCost', 'Reason',
        'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn',
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'AdjustmentId', 'Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function reason()
    {
        return $this->belongsTo(CodeDetail::class, 'Reason', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'Branch', 'Id');
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class, 'Item', 'ItemID');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'Item', 'Id');
    }
}
