<?php

namespace App\Models\Inventory;

use App\Traits\Model\ImageTrait;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Inventory\ItemCategories;

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
                           'ImageId',
                           'ItemDescription',
                           'DocumentUpload',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                           'CreatedOn',
                           'ModifiedOn',
                          ];

    protected $casts = [
                            'ItemCode'       => 'string',
                            'BarCode'       => 'string',
                            'ItemName'      => 'string',
                            'ItemType'      => 'string',
                            'Category'      => 'int',
                            'UOM'           => 'string',
                            'InventoryType' => 'string',
                            'ImageId' => 'integer',
                            'ItemDescription' => 'string',
                            'DocumentUpload' => 'string',
                            'CreatedBy'     => 'integer',
                            'ModifiedBy'    => 'integer',
                            'DeletedBy'     => 'integer',
                            'CreatedOn'     => 'datetime',
                            'ModifiedOn'    => 'datetime',

                        ];

       

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


    
}

