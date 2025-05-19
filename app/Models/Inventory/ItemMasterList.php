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
                           'ImageUpload',
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
                            'ImageUpload' => 'string',
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


protected static function booted()
{
   static::creating(function ($item) {
    if (empty($item->ItemCode)) {
        $latestCode = self::where('ItemCode', 'like', 'ITM-%')
            ->orderBy('ItemCode', 'desc')
            ->pluck('ItemCode')
            ->first();

        if ($latestCode) {
            // Extract number from the latest code
            $number = intval(substr($latestCode, 4)) + 1;
        } else {
            $number = 1;
        }

        $item->ItemCode = 'ITM-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
});

}


    
}

