<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class RFQ extends Model
{
    protected $table = 't_RFQ';
    protected $primaryKey = 'Id';
    public $incrementing = true;

    protected $fillable = [
        'TenderId',
        'ItemCategoryId',
        'SupplierId',
        'CreatedBy',
        'ModifiedBy',
    ];

    // Relationships
    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderId');
    }

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class, 'ItemCategoryId');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierId');
    }
}
