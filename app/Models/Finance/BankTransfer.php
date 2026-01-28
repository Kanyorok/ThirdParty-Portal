<?php

namespace App\Models\Finance;

use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransfer extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_BankTransfers';
    protected $primaryKey = 'TransferID';
    public $incrementing = true;
    protected $keyType = 'int';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FromBankAccountID', 'ToBankAccountID',
        'DocDate', 'CurrencyID', 'ExchangeRate', 'Amount', 'AmountBase',
        'ClearingGLAccountID', 'Reference', 'Narration', 'Status', 'IsActive',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'Amount' => 'decimal:2',
        'AmountBase' => 'decimal:2',
        'ExchangeRate' => 'decimal:6',
        'DocDate' => 'date',
    ];

    // ✅ Required by your codebase
    public static function getPrimaryKey(): string
    {
        return 'TransferID';
    }

    // Relationships
    public function fromAccount()
    {
        return $this->belongsTo(BankAccount::class, 'FromBankAccountID', 'AccountID');
    }

    public function toAccount()
    {
        return $this->belongsTo(BankAccount::class, 'ToBankAccountID', 'AccountID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'Id');
    }
}
