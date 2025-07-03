<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Core\Branch;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;



class StockAdjustment extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    

    protected $table = 't_StockAdjustments';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'AdjustmentId',
        'AdjustmentDate',
        'Branch',
        'Reason',
        'AdjustedBy',
        'Status',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn'
    ];

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'AdjustedBy', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class, 'AdjustmentId', 'Id');
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
    public static function getPrimaryKey(): string
    {
        return 'Id';
    }
    public function reason()
{
    return $this->belongsTo(CodeDetail::class, 'Reason', 'Id');
}

    


}
