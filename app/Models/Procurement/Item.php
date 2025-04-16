<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 't_items';
    protected $fillable = [
                           'Name',
                           'Type',
                           'Description',
                           'CategoryId',
                           'UOM',
                           'UnitPrice',
                           'ServiceScope',
                           'CreatedBy',
                           'ModifiedBy',
                           'UniqueCode',
                           'Currency',
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
