<?php

namespace App\Models\Finance;

use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_BankTransactions';
    protected $primaryKey = 'BankTxnID';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'BankAccountID','TransactionTypeID',
        'DocDate','CurrencyID','ExchangeRate',
        'Amount','AmountBase','Reference','Narration',
        'Status','IsActive','CreatedBy','ModifiedBy','DeletedBy',
    ];

    protected $casts = [
        'IsActive'     => 'boolean',
        'Amount'       => 'decimal:2',
        'AmountBase'   => 'decimal:2',
        'ExchangeRate' => 'decimal:6',
        'DocDate'      => 'date',
    ];

    // Required by your codebase pattern
    public static function getPrimaryKey(): string
    {
        return 'BankTxnID';
    }

    // Relationships
    public function bankAccount()  { return $this->belongsTo(BankAccount::class, 'BankAccountID', 'AccountID'); }
    public function currency()     { return $this->belongsTo(Currency::class, 'CurrencyID', 'Id'); }
    public function txnType()      { return $this->belongsTo(FinanceTransactionTypes::class, 'TransactionTypeID', 'Id'); }
}
