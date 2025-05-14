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
    protected $primaryKey = 'Id';

    protected $fillable = [
                           'ItemCode',
                           'BarCode',
                           'ItemName',
                           'ItemType',
                           'Category',
                           'SubCategory',
                           'UOM',
                           'InventoryType',
                           'ImageUpload',
                           'ItemDescription',
                           'DocumentUpload'
                          ];

    protected $casts = [
                            'ItemCode'       => 'string',
                            'BarCode'       => 'string',
                            'ItemName'      => 'string',
                            'ItemType'      => 'string',
                            'Category'      => 'string',
                            'SubCategory'   => 'string',
                            'UOM'           => 'string', 
                            'InventoryType' => 'string',
                            'ImageUpload' => 'string',
                            'ItemDescription' => 'string',
                            'DocumentUpload' => 'string',

                        ];  
                        
    public function category()
    {
        return $this->belongsTo(ItemCategories::class, 'Category', 'id');
    }      
    
    public function subcategory()
    {
        return $this->belongsTo(ItemSubCategories::class, 'SubCategory', 'Id');
    }  
}

