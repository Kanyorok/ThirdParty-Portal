<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Inventory\TransactionTransfer;
use App\Enums\Inventory\Transfers;

class TransactionReceipt extends Model
{
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_TransactionReceipts';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'TransferId',
        'ReceivedBy',
        'ReceivedDate',
        'Status',
        'GeneralRemarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'Status' => Transfers::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'ReceiptId';
    }

    public function transfer()
    {
        return $this->belongsTo(TransactionTransfer::class, 'TransferId', 'Id');
    }

    public function items()
    {
        return $this->hasMany(TransactionReceiptItem::class, 'ReceiptId', 'Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'ReceivedBy', 'Id');
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
