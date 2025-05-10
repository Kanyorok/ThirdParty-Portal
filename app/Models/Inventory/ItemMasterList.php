<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;

class ItemMasterList extends Model
{
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_ItemMasterList';
    protected $primaryKey = 'ItemCode';
    protected $keyType = 'string';

    protected $fillable = [
                           'ItemCode',
                           'BarCode',
                           'ItemName',
                           'ItemType',
                           'Category',
                           'SubCategory',
                           'UOM',
                           'InventoryType',
                          ];

    protected $casts = [
                            'ItemCode'       => 'string',
                            'BarCode'       => 'string',
                            'ItemName'      => 'string',
                            'ItemType'      => 'string',
                            'Category'      => 'string',
                            'SubCategory'   => 'string',
                            'UOM'           => 'string', 
                            'InventoryType' => 'string'
                        ];                
}
