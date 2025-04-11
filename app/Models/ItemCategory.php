<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 't_items_categories';
    protected $fillable = ['name',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
