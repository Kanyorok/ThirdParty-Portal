<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Core\Branch;
use App\Models\Inventory\StockAdjustment;
use App\Models\Auth\User;

class StockAdjustmentItem extends Model
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_StockAdjustmentItems';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'AdjustmentId', 'Item', 'AdjustmentQty', 'Remarks',
        'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn'
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'AdjustmentId', 'Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');   

    }
    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }
      public function branch()
    {
        return $this->belongsTo(Branch::class, 'Branch', 'Id');
    }

}
