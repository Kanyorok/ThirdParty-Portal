<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class GoodReceipt extends Model
{
    protected $table = 't_GoodsReceipts';
    protected $fillable = [
                           'PONo',
                           'Description',
                           'ItemNo',
                           'ItemName',
                           'ItemDescription',
                           'ItemCategory',
                           'UOM',
                           'POQTY',
                           'ReceivedQTY',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'CategoryId');
    }

    public function scopeGoods($query)
    {
        return $query->where('type', 'good');
    }

    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }
}
