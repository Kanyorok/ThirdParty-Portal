<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Core\Approval\CodeDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


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
        'AdjustedBy',
        'Status',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn'
    ];

    public static function getPrimaryKey(): string
    {
        return 'stockadjustmentId';
    }

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'AdjustedBy', 'Id');
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

    // public static function getPrimaryKey(): string
    // {
    //     return 'Id';
    // }


}
