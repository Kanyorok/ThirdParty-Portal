<?php


namespace App\Models\Inventory;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\Branch;
use App\Models\Inventory\store;
use App\Models\Auth\User;


class StockTake extends Model
{
    use SoftDeletes, UserActorTrait;
    //
   protected $table = 't_StockTake';
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'BranchId',
        'StoreId', 
        'CountedBy',
        'CountDate',                                                                                                
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
       
        ];
        public static function getPrimaryKey(): string
    {
        return 'StockTakeId';
    }
        public function ItemStoreId()
    {
        return $this->belongsTo(StockItem::class, 'Store', 'Id');
    }
        public function ItemBranchId()
    {
        return $this->belongsTo(StockItem::class, 'Branch', 'Id');
    }
        public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchId', 'Id');
    }
        public function store()
    {
        return $this->belongsTo(Store::class, 'StoreId', 'Id');

    }
        public function user()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

}