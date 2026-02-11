<?php

namespace App\Models\Inventory;

use App\Models\Core\Approval\CodeDetail;
use App\Models\DMS\Image;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemMasterList extends Model
{
    use UserActorTrait;
    use SoftDeletes;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_Items';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'ItemsId';
    }

    protected $fillable = [
        'ItemCode',
        'BarCode',
        'ItemName',
        'ItemType',
        'UOM',
        'InventoryType',
        'Category',
        'Status',
        'ImageId',
        'ItemDescription',
        'ItemPrice',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'ItemCode' => 'string',
        'BarCode' => 'string',
        'ItemName' => 'string',
        'ItemType' => 'integer',
        'UOM' => 'integer',
        'InventoryType' => 'integer',
        'Category' => 'integer',
        'Status' => 'integer',
        'ImageId' => 'integer',
        'ItemDescription' => 'string',
        'ItemPrice' => 'integer',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function inUse(): bool
    {
        return $this->stockItems()->exists()
            || $this->transferItems()->exists()
            || $this->receiptItems()->exists()
            || $this->requisitionItems()->exists();
    }

    public function category()
    {
        return $this->belongsTo(ItemCategories::class, 'Category', 'Id');
    }

    public function parentCategory()
    {
        return $this->category ? $this->category->parent() : null;
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'ImageId', 'ImageID');
    }

    public function status()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'ItemType', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOM', 'Id');
    }

    public function price()
    {
        return $this->belongsTo(PriceManagement::class, 'ItemPrice', 'Id');
    }

    public function inventoryType()
    {
        return $this->belongsTo(InventoryType::class, 'InventoryType', 'Id');
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class, 'ItemID', 'Id');
    }

    public function transferItems()
    {
        return $this->hasMany(TransactionTransferItem::class, 'Item', 'Id');
    }

    public function receiptItems()
    {
        return $this->hasMany(TransactionReceiptItem::class, 'Item', 'Id');
    }

    public function requisitionItems()
    {
        return $this->hasMany(InterBranchRequisitionItem::class, 'Item', 'Id');
    }
}
