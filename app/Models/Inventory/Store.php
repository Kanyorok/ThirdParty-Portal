<?php
namespace App\Models\Inventory;
use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Inventory\StockItem;

class Store extends Model
{
    use UserActorTrait, SoftDeletes;
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    
    protected $connection = 'sqlsrv';
    protected $table = 't_Stores';
    protected $primaryKey = 'Id';
    
    public static function getPrimaryKey(): string
    {
        return 'StoresId';
    }
    
    protected $fillable = [
        'StoreID',
        'StoreName',
        'BranchID',
        'Status',
        'IsMainStore',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];
    
    protected $casts = [
        'StoreID' => 'string',
        'StoreName' => 'string',
        'BranchID' => 'integer',
        'Status' => 'string',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];
    
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id');
    }
    
    public function stockItems()
    {
        return $this->hasMany(StockItem::class, 'Store', 'Id');
    }

    public function hasStockItems(): bool
    {
        return $this->stockItems()->exists();
    }
}