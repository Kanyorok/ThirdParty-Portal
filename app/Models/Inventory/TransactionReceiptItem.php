<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\TransactionTransferItem;

class TransactionReceiptItem extends Model
{
    use SoftDeletes;


    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_TransactionReceiptItems';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';

protected $fillable = [
    'item',
    'ReceivedQty',
    'DamagedQty',
    'DispatchedQty',   
    'Discrepancy', 
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
