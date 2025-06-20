<?php

namespace App\Models\Inventory;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTakeLines extends Model
{
    //
    use UserActorTrait, SoftDeletes;

   protected $table = 't_StockTakeLines';
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'StockTakeId',
        'ItemId',
        'ActualQuantity',
        'CountedQuantity',
        'Remarks',                                                                                                 
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
       
        ];

    public static function getPrimaryKey(): string
    {
        return 'StockTakeLineId';
    }
    public function stockTake()
    {
        return $this->belongsTo(StockTake::class, 'StockTakeId', 'Id');
    }

    public function item()
    {
        return $this->belongsTo(StockItem::class, 'ItemId', 'Id');
    }
    public function itemmaster()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }
}