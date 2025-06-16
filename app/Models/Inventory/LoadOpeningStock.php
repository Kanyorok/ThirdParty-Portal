<?php

namespace App\Models\Inventory;

use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoadOpeningStock extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_LoadOpeningStock';
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'BranchId',
        'StoreId',
        'ItemCode',
        'Date',
        'Quantity',
        'UOM',
        'Value',
        'Remarks', 
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'OpeningStockId';
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class,'ItemCode','ItemCode');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class,'BranchId','Id');
    }
        public function store()
    {
        return $this->belongsTo(Store::class,'StoreId','Id');
    }
        public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class,'UOM','Id');
    }
}
