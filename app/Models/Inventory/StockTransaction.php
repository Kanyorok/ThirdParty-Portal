<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransaction extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_StockTransactions';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';

    protected $dates = ['TransactionDate', 'DeletedOn'];

    protected $fillable = [
        'SKUID',
        'TransactionType',
        'ReferenceID',
        'ItemID',
        'StoreID',
        'BranchID',
        'UnitCost',
        'UOMID',
        'QuantityIn',
        'QuantityOut',
        'BalanceQty',
        'TotalCost',
        'TransactionDate',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'StockTransactionId';
    }

    public function scopeActive($query)
    {
        return $query->whereNull('DeletedOn');
    }

    public function scopeBranch($query, $branchId)
    {
        if ($branchId) {
            return $query->where('BranchID', $branchId);
        }

        return $query;
    }

    public function scopeDateRange($query, $fromDate, $toDate)
    {
        if ($fromDate && $toDate) {
            return $query->whereBetween('TransactionDate', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            return $query->whereDate('TransactionDate', '>=', $fromDate);
        } elseif ($toDate) {
            return $query->whereDate('TransactionDate', '<=', $toDate);
        }

        return $query;
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'StoreID', 'Id');
    }

    public function transactionTypeDetail()
    {
        return $this->belongsTo(CodeDetail::class, 'TransactionType', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOMID', 'Id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id');
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

    public function reason()
    {
        return $this->belongsTo(CodeDetail::class, 'Reason', 'Id');
    }
}
