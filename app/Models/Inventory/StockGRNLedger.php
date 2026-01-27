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

    // Relationship to parent ledger (for tracking transfers)
    public function parentLedger()
    {
        return $this->belongsTo(StockGRNLedger::class, 'ParentLedgerId');
    }

    // Relationship to child ledgers (transferred batches)
    public function childLedgers()
    {
        return $this->hasMany(StockGRNLedger::class, 'ParentLedgerId');
    }

    // Get original GRN ID (traverse up the chain)
    public function getOriginalGrnIdAttribute()
    {
        $current = $this;
        while ($current->parentLedger) {
            $current = $current->parentLedger;
        }
        return $current->GRNID;
    }

    // Get original goods receipt
    public function getOriginalGoodsReceiptAttribute()
    {
        $current = $this;
        while ($current->parentLedger) {
            $current = $current->parentLedger;
        }
        return $current->goodsReceipt;
    }
}