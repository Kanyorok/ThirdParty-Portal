<?php

namespace App\Models\Inventory;

use App\Models\DMS\Image;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\PriceManagement;
use App\Models\Core\CodeDetail;

class ItemMasterList extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_Items';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'ItemsId';
    }

    protected $fillable = [
        'BarCode',
        'ItemName',
        'ItemType',
        'UOM',
        'InventoryType',
        'Category',
        'Status',
        'ImageId',
        'ItemDescription',
        'DocumentUpload',
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
        'DocumentUpload' => 'string',
        'ItemPrice' => 'string',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    // Relationships


public function inUse(): bool
{
    return $this->stockItems()->exists()
        || $this->transferItems()->exists()
        || $this->receiptItems()->exists()
        || $this->requisitionItems()->exists();
}


    public function category()
    {
        return $this->belongsTo(ItemCategories::class, 'Category');
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

    // App\Models\Inventory\ItemMasterList.php

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
