<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class Cashbook extends Model
{
    protected $table = 't_Cashbook';
    protected $primaryKey = 'CashbookID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';

    protected $fillable = [
        'BankAccountID', 'CurrencyID', 'ExchangeRate',
        'DocDate', 'DocNo', 'EntryType', 'PartyType', 'PartyID', 'PartyName',
        'Reference', 'Narration', 'Amount', 'AmountBase',
        'SourceModule', 'SourceID', 'IsSystemGenerated',
        'Status'
    ];

    protected $casts = [
        'IsSystemGenerated' => 'boolean',
        'Amount' => 'decimal:2',
        'AmountBase' => 'decimal:2',
        'ExchangeRate' => 'decimal:6',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'PostedOn' => 'datetime',
        'VoidedOn' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            $m->CreatedBy = auth()->id();
        });
        static::updating(function ($m) {
            $m->ModifiedBy = auth()->id();
        });
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'BankAccountID', 'AccountID')
            ->with(['bank', 'branch', 'currency']);
    }

    public function currency()
    {
        return $this->belongsTo(\App\Models\Core\Currency::class, 'CurrencyID', 'Id');
    }

    public function lines()
    {
        return $this->hasMany(CashbookLine::class, 'CashbookID', 'CashbookID');
    }
}
