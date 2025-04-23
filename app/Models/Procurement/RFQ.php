<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Procurement\Tender;
use App\Models\Procurement\ItemCategory;
use App\Models\Procurement\Supplier;

class RFQ extends Model
{
    protected $table = 't_RFQ';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedAt';
    public const UPDATED_AT = 'UpdatedAt';

    protected $fillable = [
        'TenderId', 'ItemCategoryId', 'Suppliers',
    ];

    protected $casts = [
        'Suppliers' => 'array',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderId');
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'ItemCategoryId');
    }
}
