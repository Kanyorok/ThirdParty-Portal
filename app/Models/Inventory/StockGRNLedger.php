<?php

namespace App\Models\Inventory;

use App\Models\Core\Branch;
use App\Models\Procurement\GoodsReceipt;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockGRNLedger extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_StockGRNLedger';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'GRNID',
        'GoodsReceiptId',
        'StockItemId',
        'ItemNo',
        'SKUCode',
        'ReceivedQTY',
        'RemainingQTY',
        'UnitPrice',
        'Store',
        'Branch',
        'ReceivedDate',
        'SourceType',
        'SourceReference',
        'ParentLedgerId',
        'CreatedBy',
        'CreatedOn',
        'ModifiedOn',
        'DeletedOn',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'stockGRNLedgerId';
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'GoodsReceiptId');
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class, 'StockItemId');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemNo', 'Id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'Store', 'Id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'Branch', 'Id');
    }

    public function parentLedger()
    {
        return $this->belongsTo(StockGRNLedger::class, 'ParentLedgerId');
    }

    public function childLedgers()
    {
        return $this->hasMany(StockGRNLedger::class, 'ParentLedgerId');
    }

    public function getOriginalGrnIdAttribute()
    {
        $current = $this;
        while ($current->parentLedger) {
            $current = $current->parentLedger;
        }

        return $current->GRNID;
    }

    public function getOriginalGoodsReceiptAttribute()
    {
        $current = $this;
        while ($current->parentLedger) {
            $current = $current->parentLedger;
        }

        return $current->goodsReceipt;
    }
}
