<?php

namespace App\Models\Inventory;

use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransaction extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    /**
     * The table associated with the model.
     *
     * @var string
     */

    protected $table = 't_StockTransactions';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
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
        'DeletedOn'
    ];

    public static function getPrimaryKey(): string
    {
        return 'stocktransactionId';
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'StoreID', 'Id');
    }

    public function transactionType()
    {
        return $this->belongsTo(CodeDetail::class, 'TransactionType', 'Id');
    }

    public function unitCost()
    {
        return $this->belongsTo(Pricing::class, 'UnitCost', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOMID', 'Id');
    }

    public function branchId()
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

