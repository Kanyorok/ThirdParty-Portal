<?php


namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Branch;
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
        public function store()
        {
            return $this->belongsTo(Store::class, 'BranchId', 'Id');
        }
        public function branch()
        {
            return $this->belongsTo(Branch::class, 'StoreId', 'Id');
        }
        public function createdby()
        {
            return $this->belongsTo(User::class, 'CreatedBy', 'Id');
        }
        public function countedby()
        {
            return $this->belongsTo(User::class, 'CountedBy', 'Id');
        }
        public function lines()
        {
            return $this->hasMany(StockTakeLines::class, 'StockTakeId');
        }

}