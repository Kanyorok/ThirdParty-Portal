<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 't_items';
    protected $fillable = [
                           'name',
                           'type',
                           'description',
                           'category_id',
                           'unit_of_measure',
                           'unit_price',
                           'service_scope',
                           'CreatedBy',
                           'ModifiedBy',
                          ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
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
