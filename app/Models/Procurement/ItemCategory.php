<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 't_ItemCategories';
    protected $fillable = [
                           'Name',
                           'Description',
                           'CreatedBy',
                           'ModifiedBy',
                          ];

    public function items()
    {
        return $this->hasMany(Item::class, 'CategoryId');
    }

    public function supplier ()
    {
        return $this->hasMany(Supplier::class, 'CategoryId');
    }
}
