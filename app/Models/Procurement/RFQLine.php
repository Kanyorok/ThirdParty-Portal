<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\ItemCategories;
use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;

class RFQLine extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_RFQLines';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQLineNo', 'RequisitionId', 'RequisitionLineId', 'RFQId', 'ItemId', 'UOM', 'Quantity', 'ItemName', 'ItemCategoryId', 'CreatedOn', 'ModifiedOn', 'CreatedBy', 'ModifiedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'RFQLineId';
    }

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQId');
    }

    public function category()
    {
        return $this->belongsTo(ItemCategories::class, 'ItemCategoryId');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 't_RFQ_Supplier', 'RFQId', 'SupplierId')
            ->withPivot('Status')
            ->withTimestamps();
    }
}
