<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Procurement\RequisitionLines;

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

    public function requisitionLines()
    {
        return $this->hasMany(RequisitionLines::class, 'CategoryId', 'Id');
    }

}
