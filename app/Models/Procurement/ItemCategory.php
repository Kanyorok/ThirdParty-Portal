<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 't_Item_categories';
    protected $fillable = [
                           'name',
                           'description',
                           'CreatedBy',
                           'ModifiedBy',
                          ];

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
