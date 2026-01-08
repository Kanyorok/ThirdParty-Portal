<?php

namespace App\Models\Inventory;

use App\Models\Procurement\GoodsReceipt;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\Store;
use App\Models\Inventory\ItemMasterList;
use App\Models\Core\Branch;


class StockGRNLedger extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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



}