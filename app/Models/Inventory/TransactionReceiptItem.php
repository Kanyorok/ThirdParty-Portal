<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionReceiptItem extends Model
{
    use SoftDeletes;


    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_TransactionReceiptItems';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'item',
        'ReceivedQty',
        'DamagedQty',
        'DispatchedQty',
        'Discrepancy',
        'UnitCost',
        'UOM',
        'Remarks',
        'Store',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'ReceiptId',
    ];

    public function receipt()
    {
        return $this->belongsTo(TransactionReceipt::class, 'ReceiptId', 'Id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'Item', 'Id');
    }

    public function transferItem()
    {
        return $this->belongsTo(TransactionTransferItem::class, 'Item', 'Id');
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
}
