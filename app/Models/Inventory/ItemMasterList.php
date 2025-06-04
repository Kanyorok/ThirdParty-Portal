<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;

use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\UnitOfMeasure;

class ItemMasterList extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_Items';
    protected $primaryKey = 'Id';

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
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'ItemCode'        => 'string',
        'BarCode'         => 'string',
        'ItemName'        => 'string',
        'ItemType'        => 'integer',  
        'UOM'             => 'integer',  
        'InventoryType'   => 'integer',  
        'Category'        => 'integer',
        'Status'  => 'boolean',
        'ImageId'         => 'integer',
        'ItemDescription' => 'string',
        'DocumentUpload'  => 'string',
        'Status' => 'boolean',
        'CreatedBy'       => 'integer',
        'ModifiedBy'      => 'integer',
        'DeletedBy'       => 'integer',
        'CreatedOn'       => 'datetime',
        'ModifiedOn'      => 'datetime',
    ];

    // Relationships
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
        return $this->belongsTo(\App\Models\DMS\Image::class, 'ImageId', 'ImageID');
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'ItemType', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOM', 'Id');
    }

    public function inventoryType()
    {
        return $this->belongsTo(InventoryType::class, 'InventoryType', 'Id');
    }
}
