<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\Currency;

class BankAccount extends Model
{
    protected $table = 't_BankAccounts';
    protected $primaryKey = 'AccountID';

    // Map timestamps to your audit columns
    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';

    protected $fillable = [
        'BankID','BranchID','AccountName','AccountNumber','IBAN',
        'CurrencyID','GLAccountID','OpeningBalance','CurrentBalance',
        'IsDefault','IsActive',
        // audit fields are set explicitly, not mass-assigned
    ];

    protected $casts = [
        'IsDefault' => 'boolean',
        'IsActive'  => 'boolean',
        'OpeningBalance' => 'decimal:2',
        'CurrentBalance' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn'=> 'datetime',
        'DeletedOn' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) { $m->CreatedBy  = auth()->id(); });
        static::updating(function ($m) { $m->ModifiedBy = auth()->id(); });
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'BankID', 'BankID');
    }

    public function branch()
    {
        return $this->belongsTo(BankBranch::class, 'BranchID', 'BranchID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'Id');
    }
}
